<?php

require($_SESSION["SX_BASEPATH"] . '/tcpdf/tcpdf.php');

class Meetingverslag extends TCPDF {

    public $__currentY = 0;

    public $meetingDatum;
    public $meetingGroep;
    public $meetingType;
    public $aanwezig;
    public $verontschuldigd;
    public $info;
    public $logoSSP;
    public $pageType;

    function Header() {

    $this->SetXY(60,5);
    $this->SetFillColor(255, 0, 0);
    $this->SetFont('times','B',15);
    $this->Cell(90,0,"VERSLAG VERGADERING",0,0,"",false);

    $this->SetFont('times','',10);
    $this->SetTextColor(0, 0, 0);


    $line = 15;

    $titel = 'Datum:';
    $this->SetXY(5,$line);
    $this->Cell(30,0,$titel);
    $this->Cell(25,0,"$this->meetingDatum");
    $this->Ln(5);

    $line += 5;

    $this->SetXY(5,$line);
    $this->Cell(30,0,"Groep:");
    $this->Cell(25,0,"$this->meetingGroep");
    $this->Ln(5);

    $line += 5;

    $this->SetXY(5,$line);
    $this->Cell(30,0,"Type:");
    $this->Cell(25,0,"$this->meetingType");
    $this->Ln(5);

    $line += 5;

    $this->SetXY(5,$line);
    $this->Cell(30,0,"Aanwezig:");
    $this->Cell(30,0,"$this->aanwezig");
    $this->Ln(5);


    if ($this->verontschuldigd > ' ') {
    $line += 5;

    $this->SetXY(5, $line);
    $this->Cell(30, 0, "Verontschuldigd:");
    $this->Cell(30, 0, "$this->verontschuldigd");
    $this->Ln(5);
    }

    $this->__currentY = $this->GetY();

    $this->Image($this->logoSPP, 180, 5, 20, '', 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);


    }

    function Agendapunt($pNummer, $pTitel, $pOmschrijving, $pBesluit) {

    $x = 5;
    $y = $this->__currentY + 5;

    $this->SetXY($x,$y);

    $this->SetFont('times','B',10);
    $this->Cell(10,0,"$pNummer)");

    $x += 5;
    $this->SetXY($x,$y);
    $this->SetFont('times','BU',10);
    $this->Cell(100,0,$pTitel);

    $this->__currentY = $this->GetY();

    if ($pOmschrijving > " " ) {
    $y = $this->__currentY + 5;
    $this->SetXY($x,$y);
    $this->SetFont('','I',10);
    $this->MultiCell(800, 0, $pOmschrijving);
    $this->__currentY = $this->GetY();
    $y = $this->__currentY;

    } else{

    $this->__currentY = $this->GetY();
    $y = $this->__currentY + 7;

    }

    $this->__currentY = $this->GetY();

    if ($pBesluit > " "){
    $this->SetXY($x,$y);
    $this->SetFont('','B',10);
    $this->Cell(15,0,"Besluit:");
    $this->SetFont('','',10);
    $this->MultiCell(800, 0, $pBesluit);
    }
    $this->__currentY = $this->GetY();

    return $this->__currentY;

    }

    function Actiepunt($pTitel, $pOmschrijving) {

    $x = 10;
    $y = $this->__currentY + 5;
    $this->SetXY($x, $y);

    $this->SetFont('','B',10);
    $this->Cell(15,0,"Actiepunt:");

    $x = 30;
    $this->SetXY($x, $y);
    $this->SetFont('','',10);
    $this->Cell(15,0,$pTitel);

    $this->__currentY = $this->GetY();

    if ($pOmschrijving > " "){
    $y = $this->__currentY + 5;
    $this->SetXY($x,$y);
    $this->SetFont('','I',10);
    $this->MultiCell(500, 0, $pOmschrijving);
    }

    $this->__currentY = $this->GetY();
    }

    function Footer() {

    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    //  italic 8
    $this->SetFont('','I',8);
    // Text color in gray
    $this->SetTextColor(128);
    $this->Cell(15,10,"EMA - Eenvoudige Meeting Adminisratie");
    // Page number
    $this->Cell(0,10,'Blz '.$this->PageNo() . ' / ' . $this->getAliasNbPages(),0,0,'C');

    }
}

?>