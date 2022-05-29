<?php
class PDF extends FPDF
{
    //task-future-work debug PDF class and makes sure it works okay, while fixing errors
    /*
    * current-php-code 2021-jan-3
    * internal-call
    * input-sanitized :
   */
function Header()
{
	global $title;

	// Arial bold 15
	$this->SetFont('Arial','B',15);
	// Calculate width of title and position
	$w = $this->GetStringWidth($title)+6;
	$this->SetX((210-$w)/2);
	// Colors of frame, background and text
	$this->SetDrawColor(0,80,180);
	$this->SetFillColor(230,230,0);
	$this->SetTextColor(220,50,50);
	// Thickness of frame (1 mm)
	$this->SetLineWidth(1);
	// Title
	$this->Cell($w,9,$title,1,1,'C',true);
	// Line break
	$this->Ln(10);
}

function Footer()
{
	// Position at 1.5 cm from bottom
	$this->SetY(-15);
	// Arial italic 8
	$this->SetFont('Arial','I',8);
	// Text color in gray
	$this->SetTextColor(128);
	// Page number
	$this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
}
function WriteHTML($html)
{
	// HTML parser
	$html = str_replace("\n",' ',$html);
	$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			// Text
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
				//$this->Cell('/n');
			else
				$this->Write(5,$e);
		}
		else
		{
			// Tag
			if($e[0]=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				// Extract attributes
				$a2 = explode(' ',$e);
				$tag = strtoupper(array_shift($a2));
				$attr = array();
				foreach($a2 as $v)
				{
					if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
						$attr[strtoupper($a3[1])] = $a3[2];
				}
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

function OpenTag($tag, $attr)
{
	$tag = strtoupper($tag);
	//Opening tag
    switch($tag){
        case 'STRONG':
            $this->SetStyle('B',true);
			//$this->Ln(1);
            break;
		case 'H2':
            $this->SetStyle('B',true);
			$this->Ln(6);
            break;
		case 'H3':
            $this->SetStyle('B',true);
			//$this->Ln(3);
            break;
        case 'EM':
            $this->SetStyle('I',true);
            break;
        case 'B':
        case 'I':
		case 'LI':
			$this->Ln(10);
            break;
        case 'U':
            $this->SetStyle($tag,true);
            break;
        case 'A':
            $this->HREF=$attr['HREF'];
			//$this->Ln(1);
            break;
        case 'IMG':
            if(isset($attr['SRC']) ) {
                $this->Ln(1);
				if(isset($attr['WIDTH']) && isset($attr['HEIGHT'])){
					$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
				}
				else{
					$this->Image($attr['SRC']);
				}
				$this->Ln(1);
            }
            break;
        case 'TR':
        case 'BLOCKQUOTE':
        case 'BR':
            $this->Ln(5);
            break;
        case 'P':
            $this->Ln(10);
            break;
        case 'FONT':
            if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                $coul=hex2dec($attr['COLOR']);
                $this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
                $this->issetcolor=true;
            }
            if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                $this->SetFont(strtolower($attr['FACE']));
                $this->issetfont=true;
            }
            break;
    }
}

function CloseTag($tag)
{
	$tag = strtoupper($tag);
	//Closing tag
    if($tag=='STRONG' || $tag=='h3')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
		
    if($tag=='FONT'){
        if ($this->issetcolor==true) {
            $this->SetTextColor(0);
        }
        if ($this->issetfont) {
            $this->SetFont('arial');
            $this->issetfont=false;
        }
    }
}

function SetStyle($tag, $enable)
{
	// Modify style and select corresponding font
	$this->$tag += ($enable ? 1 : -1);
	$style = '';
	foreach(array('B', 'I', 'U') as $s)
	{
		if($this->$s>0)
			$style .= $s;
	}
	$this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
	// Put a hyperlink
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}
function ChapterTitle($num, $label)
{
	// Arial 12
	$this->SetFont('Arial','',12);
	// Background color
	$this->SetFillColor(200,220,255);
	// Title
	$this->Cell(0,6,"Chapter $num : $label",0,1,'L',true);
	// Line break
	//$this->Ln(1);
}

function ChapterBody($string)
{
	
	// Times 12
	$this->SetFont('Times','',12);
	// Output justified text
	//$this->MultiCell(0,5,$txt);
	//$this->MultiCell(0,5,$txt);
	// Line break
	$string=str_replace('&nbsp;',' ',$string);
	//$string = preg_replace("/\s|&nbsp;/",'',$string);
	$this->WriteHTML($string);
	// Mention in italics
	$this->SetFont('','I');
	//$this->Cell(0,5,'(end of excerpt)');
}
function Description($txt)
{
	$this->AddPage();
	$this->SetFont('Times','',12);
	$this->MultiCell(0,5,$txt);
	$this->SetFont('','I');
}
function PrintChapter($num, $title, $txt)
{
	//$this->AddPage();
	$this->ChapterTitle($num,$title);
	$this->ChapterBody($txt);
}
}