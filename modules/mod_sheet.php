<?php

if (empty($_PAGE['request'])) {
	redirect(array('val'=>47), 'Vous devez sélectionner un personnage', 'warning');
}

$char_id = 			isset($_PAGE['request'][0]) ? (int) $_PAGE['request'][0] : 0;
$sheet_page =		isset($_PAGE['request']['page']) ? (int) $_PAGE['request']['page'] : 1;
$printer_friendly =	isset($_PAGE['request']['print']) ? true : false;
$sheet_style =		isset($_PAGE['request']['sheet']) ? $_PAGE['request']['sheet'] : 'original';
$zip =				isset($_PAGE['request']['zip']) ? true : false;
$pdf =				isset($_PAGE['request']['pdf']) ? true : false;

//MakeFont::MakeFont(ROOT.DS.'webroot'.DS.'css'.DS.'fonts'.DS.'LettrinEsteren-Regular.ttf', 'cp1252');
//MakeFont::MakeFont(ROOT.DS.'webroot'.DS.'css'.DS.'fonts'.DS.'carolingia.ttf', 'cp1252');
//MakeFont::MakeFont(ROOT.DS.'webroot'.DS.'css'.DS.'fonts'.DS.'carolingia_old.ttf', 'cp1252');
//MakeFont::MakeFont(ROOT.DS.'webroot'.DS.'css'.DS.'fonts'.DS.'times.ttf', 'cp1252');
//MakeFont::MakeFont(ROOT.DS.'webroot'.DS.'css'.DS.'fonts'.DS.'UnZialish.ttf', 'cp1252');
//MakeFont::MakeFont(ROOT.DS.'webroot'.DS.'css'.DS.'fonts'.DS.'arial.ttf', 'cp1252');

Translate::$char_id = $char_id;

if (!$char_id) { redirect(array('val'=>47), 'Aucun personnage entré', 'warning'); }
$character = new Esterenchar($char_id, 'db');
if (!$character->get()) { redirect(array('val'=>47), 'Aucun personnage trouvé', 'warning'); }

if ($pdf === true) {
	$datas = array(
		'character' => $character,
		'printer_friendly' => $printer_friendly,
		'sheet_style' => $sheet_style,
	);
	load_module('pdf', 'module', $datas);
} elseif ($zip === true) {
	$datas = array(
		'char_id' => $char_id,
		'character' => $character,
		'printer_friendly' => $printer_friendly,
		'sheet_style' => $sheet_style,
	);
	load_module('zip', 'module', $datas);
} else {
	$datas = array(
		'character' => $character,
		'printer_friendly' => $printer_friendly,
		'sheet_style' => $sheet_style,
		'sheet_page' => $sheet_page,
	);
	load_module('jpg', 'module', $datas);
}

Translate::$char_id = null;
