<?php
/**
 * Import Controller
 * Handles Excel file import
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ImportController {
    
    /**
     * Show import form
     */
    public function form() {
        requireAdmin();
        include __DIR__ . '/../views/products/import.php';
    }
    
    /**
     * Process Excel import
     */
    public function process() {
        requireAdmin();
        startSession();
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid security token. Please try again.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=import');
        }
        
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('Please select a valid Excel file.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=import');
        }
        
        $file = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        
        // Validate file extension
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            setFlashMessage('Invalid file type. Please upload an Excel file (.xlsx or .xls).', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=import');
        }
        
        try {
            // Load spreadsheet
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip header row (row 1)
            array_shift($rows);
            
            $conn = getDBConnection();
            $settings = getDefaultSettings();
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Prepare statements
            $checkStmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
            $insertStmt = $conn->prepare("INSERT INTO products 
                (sku, description, image_url, product_link, unit_price, duty, shipping_cost, box_price,
                landing_cost, margin_percentage, final_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $updateStmt = $conn->prepare("UPDATE products SET 
                description = ?, image_url = ?, product_link = ?, unit_price = ?, duty = ?, shipping_cost = ?,
                box_price = ?, landing_cost = ?, margin_percentage = ?, final_price = ?
                WHERE sku = ?");
            
            foreach ($rows as $rowIndex => $row) {
                // Expected column order:
                // 0: Sr No, 1: Image, 2: SKU, 3: Description, 4: Final Cost, 5: Proposal Margin,
                // 6: Price, 7: Selection, 8: Product Link, 9: Unit Price, 10: 30% Duty,
                // 11: Shipping Cost 5%, 12: Box Price, 13: US Landing Cost, 14: Margin, 15: Final Price
                
                if (count($row) < 16) {
                    continue; // Skip incomplete rows
                }
                
                $sku = trim($row[2] ?? '');
                
                // Skip if SKU is empty
                if (empty($sku)) {
                    $errorCount++;
                    $errors[] = "Row " . ($rowIndex + 2) . ": Empty SKU";
                    continue;
                }
                
                $description = trim($row[3] ?? '');
                $imageUrl = trim($row[1] ?? '');
                $productLink = trim($row[8] ?? '');
                
                // Get unit price (column 9) or calculate from other columns
                $unitPrice = floatval($row[9] ?? 0);
                
                // If unit price is 0, try to calculate from landing cost
                if ($unitPrice == 0) {
                    $landingCost = floatval($row[13] ?? 0);
                    if ($landingCost > 0) {
                        // Reverse calculate: E = A + B + C + D
                        // B = A * 0.30, C = (A + B) * 0.05
                        // E = A + (A * 0.30) + ((A + A * 0.30) * 0.05) + D
                        // E = A + 0.30A + 0.05A + 0.015A + D
                        // E = 1.365A + D
                        // A = (E - D) / 1.365
                        $boxPrice = floatval($row[12] ?? $settings['default_box_price']);
                        $unitPrice = ($landingCost - $boxPrice) / 1.365;
                    }
                }
                
                // Use provided values or defaults
                $dutyPercentage = $settings['default_duty_percentage'];
                $shippingPercentage = $settings['default_shipping_percentage'];
                $boxPrice = floatval($row[12] ?? $settings['default_box_price']);
                $marginPercentage = floatval($row[14] ?? $settings['default_margin_percentage']);
                
                // Calculate pricing
                $pricing = calculatePricing($unitPrice, $dutyPercentage, $shippingPercentage, $boxPrice, $marginPercentage);
                
                // Check if product exists
                $checkStmt->bind_param("s", $sku);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $exists = $result->num_rows > 0;
                
                if ($exists) {
                    // Update existing product
                    $updateStmt->bind_param("sssddddddds",
                        $description, $imageUrl, $productLink,
                        $pricing['unit_price'], $pricing['duty'], $pricing['shipping_cost'], $pricing['box_price'],
                        $pricing['landing_cost'], $pricing['margin_percentage'], $pricing['final_price'],
                        $sku
                    );
                    
                    if ($updateStmt->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Row " . ($rowIndex + 2) . ": " . $updateStmt->error;
                    }
                } else {
                    // Insert new product
                    $insertStmt->bind_param("ssssddddddd",
                        $sku, $description, $imageUrl, $productLink,
                        $pricing['unit_price'], $pricing['duty'], $pricing['shipping_cost'], $pricing['box_price'],
                        $pricing['landing_cost'], $pricing['margin_percentage'], $pricing['final_price']
                    );
                    
                    if ($insertStmt->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Row " . ($rowIndex + 2) . ": " . $insertStmt->error;
                    }
                }
            }
            
            $checkStmt->close();
            $insertStmt->close();
            $updateStmt->close();
            
            $message = "Import completed. Success: $successCount, Errors: $errorCount";
            if (!empty($errors) && count($errors) <= 10) {
                $message .= "<br>Errors: " . implode(", ", $errors);
            }
            
            setFlashMessage($message, $errorCount > 0 ? FLASH_WARNING : FLASH_SUCCESS);
            redirect(BASE_URL . 'index.php?action=products');
            
        } catch (Exception $e) {
            setFlashMessage('Error importing file: ' . $e->getMessage(), FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=import');
        }
    }
    
    /**
     * Download sample Excel file
     */
    public function downloadSample() {
        requireAdmin();
        
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(40);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(40);
            $sheet->getColumnDimension('J')->setWidth(12);
            $sheet->getColumnDimension('K')->setWidth(12);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(12);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(10);
            $sheet->getColumnDimension('P')->setWidth(12);
            
            // Header row
            $headers = [
                'Sr No',
                'Image',
                'SKU',
                'Description',
                'Final Cost',
                'Proposal Margin',
                'Price',
                'Selection',
                'Product Link',
                'Unit Price',
                '30% Duty',
                'Shipping Cost 5%',
                'Box Price',
                'US Landing Cost',
                'Margin',
                'Final Price'
            ];
            
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getStyle($col . '1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('4472C4');
                $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle($col . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $col++;
            }
            
            // Sample data rows
            $sampleData = [
                [
                    1,
                    'https://example.com/image1.jpg',
                    'SKU001',
                    'Sample Product 1 - Electronics Item',
                    0,
                    35,
                    0,
                    0,
                    'https://www.alibaba.com/product-detail/sample-product-1.html',
                    10.00,
                    3.00,
                    0.65,
                    1.00,
                    14.65,
                    35,
                    23
                ],
                [
                    2,
                    'https://example.com/image2.jpg',
                    'SKU002',
                    'Sample Product 2 - Gift Item',
                    0,
                    35,
                    0,
                    0,
                    'https://www.alibaba.com/product-detail/sample-product-2.html',
                    25.50,
                    7.65,
                    1.66,
                    1.00,
                    35.81,
                    35,
                    55
                ],
                [
                    3,
                    'https://example.com/image3.jpg',
                    'SKU003',
                    'Sample Product 3 - Promotional Item',
                    0,
                    40,
                    0,
                    0,
                    'https://www.alibaba.com/product-detail/sample-product-3.html',
                    5.00,
                    1.50,
                    0.33,
                    1.00,
                    7.83,
                    40,
                    13
                ]
            ];
            
            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    if (in_array($col, ['J', 'K', 'L', 'M', 'N', 'P'])) {
                        $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                    $col++;
                }
                $row++;
            }
            
            // Apply borders
            $lastRow = count($sampleData) + 1;
            $sheet->getStyle('A1:P' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            
            // Freeze header row
            $sheet->freezePane('A2');
            
            // Output
            $filename = 'product_import_sample_' . date('Ymd') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            setFlashMessage('Error generating sample file: ' . $e->getMessage(), FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=import');
        }
    }
}

