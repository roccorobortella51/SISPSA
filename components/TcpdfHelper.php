<?php

namespace app\components;

use Yii;
// Load TCPDF BEFORE the class definition
require_once Yii::getAlias('@vendor/tecnickcom/tcpdf/tcpdf.php');

class TcpdfHelper extends \TCPDF
{
    private $reportTitle = '';
    private $reportSubtitle = '';

    public function setReportTitle($title)
    {
        $this->reportTitle = $title;
    }

    public function setReportSubtitle($subtitle)
    {
        $this->reportSubtitle = $subtitle;
    }

    // Override Header
    public function Header()
    {
        // Only show header on first page
        if ($this->page == 1) {
            // Main title
            $this->SetFont('helvetica', 'B', 16);
            $this->SetTextColor(44, 62, 80); // #2c3e50
            $this->Cell(0, 10, $this->reportTitle, 0, 1, 'C', 0, '', 0, false, 'M', 'M');

            // Subtitle
            $this->SetFont('helvetica', 'B', 11);
            $this->SetTextColor(0, 120, 212); // #0078d4
            $this->Cell(0, 5, $this->reportSubtitle, 0, 1, 'C', 0, '', 0, false, 'M', 'M');

            // Line separator
            $this->Line(10, 30, 200, 30, ['width' => 0.5, 'color' => array(44, 62, 80)]);

            // Reset Y position
            $this->SetY(35);
        }
    }

    // Override Footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        // Set font
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);

        // Page number
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Helper to create colored cell
    public function coloredCell($w, $h, $txt, $border, $ln, $align, $fill, $link = '', $bgcolor = array(), $textcolor = array())
    {
        if (!empty($bgcolor)) {
            $this->SetFillColor($bgcolor[0], $bgcolor[1], $bgcolor[2]);
        }
        if (!empty($textcolor)) {
            $this->SetTextColor($textcolor[0], $textcolor[1], $textcolor[2]);
        } else {
            $this->SetTextColor(0, 0, 0);
        }

        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);

        // Reset colors
        $this->SetTextColor(0);
        $this->SetFillColor(255);
    }
}
