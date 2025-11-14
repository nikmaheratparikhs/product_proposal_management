<?php
/**
 * Export Controller
 * Handles proposal exports (Excel, Word, PowerPoint)
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory as PresIOFactory;
use PhpOffice\PhpPresentation\Style\Alignment as PresAlignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill as PresFill;

class ExportController {
    
    /**
     * Export proposal to Excel
     */
    public function exportExcel() {
        requireLogin();
        
        $proposalId = intval($_GET['id'] ?? 0);
        if ($proposalId <= 0) {
            setFlashMessage('Invalid proposal ID.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $proposal = $this->getProposalData($proposalId);
        if (!$proposal) {
            setFlashMessage('Proposal not found.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);  // SR No
        $sheet->getColumnDimension('B')->setWidth(20);  // Image
        $sheet->getColumnDimension('C')->setWidth(20);  // SKU
        $sheet->getColumnDimension('D')->setWidth(40);  // Description
        $sheet->getColumnDimension('E')->setWidth(15);  // Unit Price
        $sheet->getColumnDimension('F')->setWidth(15);  // Landing Cost
        $sheet->getColumnDimension('G')->setWidth(15);  // Margin %
        $sheet->getColumnDimension('H')->setWidth(15);  // Final Price
        $sheet->getColumnDimension('I')->setWidth(12);  // Quantity
        $sheet->getColumnDimension('J')->setWidth(15);  // Total
        
        $row = 1;
        
        // Logo and header
        if (!empty($proposal['settings']['company_logo'])) {
            $logoPath = BASE_PATH . '/public/' . $proposal['settings']['company_logo'];
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath($logoPath);
                $drawing->setHeight(50);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);
                $row = 3;
            }
        }
        
        // Title
        $sheet->setCellValue('A' . $row, 'PRODUCT PROPOSAL');
        $sheet->mergeCells('A' . $row . ':J' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        
        // Event name
        if (!empty($proposal['event_name'])) {
            $sheet->setCellValue('A' . $row, 'Event: ' . $proposal['event_name']);
            $sheet->mergeCells('A' . $row . ':J' . $row);
            $row++;
        }
        
        // Customer name
        if (!empty($proposal['customer_name'])) {
            $sheet->setCellValue('A' . $row, 'Customer: ' . $proposal['customer_name']);
            $sheet->mergeCells('A' . $row . ':J' . $row);
            $row++;
        }
        
        // Date
        $sheet->setCellValue('A' . $row, 'Date: ' . date('Y-m-d', strtotime($proposal['created_at'])));
        $sheet->mergeCells('A' . $row . ':J' . $row);
        $row += 2;
        
        // Table header
        $headers = ['SR No', 'Image', 'SKU', 'Description', 'Unit Price', 'Landing Cost', 'Margin %', 'Final Price', 'Quantity', 'Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $row++;
        
        // Table data
        $grandTotal = 0;
        $srNo = 1;
        foreach ($proposal['items'] as $item) {
            $col = 'A';
            // SR No
            $sheet->setCellValue($col . $row, $srNo);
            $col++;
            // Image - Add =IMAGE() formula and URL
            $imageUrl = !empty($item['image_url']) ? $item['image_url'] : '';
            if (!empty($imageUrl)) {
                // Set the formula for Google Sheets/Excel - =IMAGE(url)
                // This will display the image in Google Sheets and Excel
                // The URL is already in the formula, so users can see it in the formula bar
                $sheet->setCellValueExplicit($col . $row, '=IMAGE("' . $imageUrl . '")', DataType::TYPE_FORMULA);
            } else {
                $sheet->setCellValue($col . $row, '');
            }
            $col++;
            // SKU
            $sheet->setCellValue($col . $row, $item['sku']);
            $col++;
            // Description
            $sheet->setCellValue($col . $row, $item['description']);
            $col++;
            // Unit Price
            $sheet->setCellValue($col . $row, formatCurrency($item['unit_price']));
            $col++;
            // Landing Cost
            $sheet->setCellValue($col . $row, formatCurrency($item['landing_cost']));
            $col++;
            // Margin %
            $margin = $item['custom_margin'] ?? $item['margin_percentage'];
            $sheet->setCellValue($col . $row, formatPercentage($margin));
            $col++;
            // Final Price
            $sheet->setCellValue($col . $row, formatCurrency($item['final_price']));
            $col++;
            // Quantity
            $sheet->setCellValue($col . $row, $item['quantity']);
            $col++;
            // Total
            $total = $item['final_price'] * $item['quantity'];
            $sheet->setCellValue($col . $row, formatCurrency($total));
            $grandTotal += $total;
            $srNo++;
            $row++;
        }
        
        // Grand total
        $row++;
        $sheet->setCellValue('I' . $row, 'GRAND TOTAL:');
        $sheet->getStyle('I' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('J' . $row, formatCurrency($grandTotal));
        $sheet->getStyle('J' . $row)->getFont()->setBold(true);
        
        // Notes
        if (!empty($proposal['notes'])) {
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Notes:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A' . $row, $proposal['notes']);
            $sheet->mergeCells('A' . $row . ':J' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        }
        
        // Apply borders
        $sheet->getStyle('A1:J' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Output
        $filename = 'proposal_' . $proposalId . '_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export proposal to Word
     */
    public function exportWord() {
        requireLogin();
        
        $proposalId = intval($_GET['id'] ?? 0);
        if ($proposalId <= 0) {
            setFlashMessage('Invalid proposal ID.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $proposal = $this->getProposalData($proposalId);
        if (!$proposal) {
            setFlashMessage('Proposal not found.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Logo
        if (!empty($proposal['settings']['company_logo'])) {
            $logoPath = BASE_PATH . '/public/' . $proposal['settings']['company_logo'];
            if (file_exists($logoPath)) {
                $section->addImage($logoPath, ['width' => 150, 'height' => 50]);
            }
        }
        
        // Title
        $section->addText('PRODUCT PROPOSAL', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addTextBreak(1);
        
        // Event and customer info
        if (!empty($proposal['event_name'])) {
            $section->addText('Event: ' . $proposal['event_name']);
        }
        if (!empty($proposal['customer_name'])) {
            $section->addText('Customer: ' . $proposal['customer_name']);
        }
        $section->addText('Date: ' . date('Y-m-d', strtotime($proposal['created_at'])));
        $section->addTextBreak(1);
        
        // Table
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Header row
        $table->addRow();
        $table->addCell(800)->addText('SR No', ['bold' => true]);
        $table->addCell(2000)->addText('Image URL', ['bold' => true]);
        $table->addCell(1500)->addText('SKU', ['bold' => true]);
        $table->addCell(3000)->addText('Description', ['bold' => true]);
        $table->addCell(1500)->addText('Unit Price', ['bold' => true]);
        $table->addCell(1500)->addText('Landing Cost', ['bold' => true]);
        $table->addCell(1200)->addText('Margin %', ['bold' => true]);
        $table->addCell(1500)->addText('Final Price', ['bold' => true]);
        $table->addCell(1000)->addText('Qty', ['bold' => true]);
        $table->addCell(1500)->addText('Total', ['bold' => true]);
        
        // Data rows
        $grandTotal = 0;
        $srNo = 1;
        foreach ($proposal['items'] as $item) {
            $table->addRow();
            $table->addCell()->addText($srNo);
            // Image URL
            $imageUrl = !empty($item['image_url']) ? $item['image_url'] : 'N/A';
            $table->addCell()->addText($imageUrl);
            $table->addCell()->addText($item['sku']);
            $table->addCell()->addText($item['description']);
            $table->addCell()->addText(formatCurrency($item['unit_price']));
            $table->addCell()->addText(formatCurrency($item['landing_cost']));
            $margin = $item['custom_margin'] ?? $item['margin_percentage'];
            $table->addCell()->addText(formatPercentage($margin));
            $table->addCell()->addText(formatCurrency($item['final_price']));
            $table->addCell()->addText($item['quantity']);
            $total = $item['final_price'] * $item['quantity'];
            $table->addCell()->addText(formatCurrency($total));
            $grandTotal += $total;
            $srNo++;
        }
        
        // Grand total row
        $table->addRow();
        $table->addCell(8000, ['bgColor' => 'E0E0E0'])->addText('GRAND TOTAL:', ['bold' => true]);
        $table->addCell(1500, ['bgColor' => 'E0E0E0'])->addText(formatCurrency($grandTotal), ['bold' => true]);
        
        $section->addTextBreak(1);
        
        // Notes
        if (!empty($proposal['notes'])) {
            $section->addText('Notes:', ['bold' => true]);
            $section->addText($proposal['notes']);
        }
        
        // Output
        $filename = 'proposal_' . $proposalId . '_' . date('YmdHis') . '.docx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export proposal to PowerPoint
     */
    public function exportPowerPoint() {
        requireLogin();
        
        $proposalId = intval($_GET['id'] ?? 0);
        if ($proposalId <= 0) {
            setFlashMessage('Invalid proposal ID.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $proposal = $this->getProposalData($proposalId);
        if (!$proposal) {
            setFlashMessage('Proposal not found.', FLASH_ERROR);
            redirect(BASE_URL . 'index.php?action=proposals');
        }
        
        $phpPresentation = new PhpPresentation();
        $slide = $phpPresentation->createSlide();
        
        // Title
        $shape = $slide->createRichTextShape()
            ->setHeight(50)
            ->setWidth(900)
            ->setOffsetX(10)
            ->setOffsetY(10);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal(PresAlignment::HORIZONTAL_CENTER);
        $textRun = $shape->createTextRun('PRODUCT PROPOSAL');
        $textRun->getFont()->setBold(true)->setSize(24);
        
        // Event and customer info
        $yPos = 70;
        if (!empty($proposal['event_name'])) {
            $shape = $slide->createRichTextShape()
                ->setHeight(20)
                ->setWidth(900)
                ->setOffsetX(10)
                ->setOffsetY($yPos);
            $shape->createTextRun('Event: ' . $proposal['event_name']);
            $yPos += 25;
        }
        
        if (!empty($proposal['customer_name'])) {
            $shape = $slide->createRichTextShape()
                ->setHeight(20)
                ->setWidth(900)
                ->setOffsetX(10)
                ->setOffsetY($yPos);
            $shape->createTextRun('Customer: ' . $proposal['customer_name']);
            $yPos += 25;
        }
        
        $shape = $slide->createRichTextShape()
            ->setHeight(20)
            ->setWidth(900)
            ->setOffsetX(10)
            ->setOffsetY($yPos);
        $shape->createTextRun('Date: ' . date('Y-m-d', strtotime($proposal['created_at'])));
        $yPos += 40;
        
        // Table header
        $headers = ['SKU', 'Description', 'Unit Price', 'Landing Cost', 'Margin %', 'Final Price', 'Qty', 'Total'];
        $colWidth = 110;
        $xPos = 10;
        
        foreach ($headers as $header) {
            $shape = $slide->createRichTextShape()
                ->setHeight(30)
                ->setWidth($colWidth)
                ->setOffsetX($xPos)
                ->setOffsetY($yPos);
            $shape->getFill()->setFillType(PresFill::FILL_SOLID)
                ->setStartColor(new Color('E0E0E0'));
            $textRun = $shape->createTextRun($header);
            $textRun->getFont()->setBold(true)->setSize(10);
            $xPos += $colWidth;
        }
        
        $yPos += 35;
        
        // Table data
        $grandTotal = 0;
        foreach ($proposal['items'] as $item) {
            $xPos = 10;
            $data = [
                $item['sku'],
                substr($item['description'], 0, 20),
                formatCurrency($item['unit_price']),
                formatCurrency($item['landing_cost']),
                formatPercentage($item['custom_margin'] ?? $item['margin_percentage']),
                formatCurrency($item['final_price']),
                $item['quantity'],
                formatCurrency($item['final_price'] * $item['quantity'])
            ];
            
            foreach ($data as $cellData) {
                $shape = $slide->createRichTextShape()
                    ->setHeight(25)
                    ->setWidth($colWidth)
                    ->setOffsetX($xPos)
                    ->setOffsetY($yPos);
                $shape->createTextRun($cellData)->getFont()->setSize(9);
                $xPos += $colWidth;
            }
            
            $grandTotal += $item['final_price'] * $item['quantity'];
            $yPos += 30;
        }
        
        // Grand total
        $shape = $slide->createRichTextShape()
            ->setHeight(30)
            ->setWidth(770)
            ->setOffsetX(10)
            ->setOffsetY($yPos);
        $shape->getFill()->setFillType(PresFill::FILL_SOLID)
            ->setStartColor(new Color('E0E0E0'));
        $textRun = $shape->createTextRun('GRAND TOTAL: ' . formatCurrency($grandTotal));
        $textRun->getFont()->setBold(true);
        
        // Notes
        if (!empty($proposal['notes'])) {
            $yPos += 40;
            $shape = $slide->createRichTextShape()
                ->setHeight(50)
                ->setWidth(900)
                ->setOffsetX(10)
                ->setOffsetY($yPos);
            $textRun = $shape->createTextRun('Notes: ' . $proposal['notes']);
            $textRun->getFont()->setBold(true);
        }
        
        // Output
        $filename = 'proposal_' . $proposalId . '_' . date('YmdHis') . '.pptx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = PresIOFactory::createWriter($phpPresentation, 'PowerPoint2007');
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Get proposal data for export
     */
    private function getProposalData($proposalId) {
        $userId = $_SESSION['user_id'];
        $isAdmin = isAdmin();
        
        $conn = getDBConnection();
        
        if ($isAdmin) {
            $stmt = $conn->prepare("SELECT * FROM proposals WHERE id = ?");
            $stmt->bind_param("i", $proposalId);
        } else {
            $stmt = $conn->prepare("SELECT * FROM proposals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $proposalId, $userId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $proposal = $result->fetch_assoc();
        $stmt->close();
        
        // Get items
        $itemsStmt = $conn->prepare("SELECT pi.*, pr.* FROM proposal_items pi 
            INNER JOIN products pr ON pi.product_id = pr.id 
            WHERE pi.proposal_id = ?");
        $itemsStmt->bind_param("i", $proposalId);
        $itemsStmt->execute();
        $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $itemsStmt->close();
        
        $proposal['items'] = $items;
        
        // Get settings
        $proposal['settings'] = getDefaultSettings();
        
        return $proposal;
    }
}

