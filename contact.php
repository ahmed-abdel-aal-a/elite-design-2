<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function respond(int $code, bool $success): never { http_response_code($code); echo json_encode(['success'=>$success]); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); respond(405,false); }
if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 8192) respond(413,false);
$data=json_decode(file_get_contents('php://input'),true);
if (!is_array($data)) respond(400,false);
if (!empty($data['website'])) respond(200,true);
$contact=trim((string)($data['contact'] ?? '')); $message=trim((string)($data['message'] ?? ''));
if (strlen($contact)<5 || strlen($contact)>160 || strlen($message)<10 || strlen($message)>4000 || preg_match('/[\r\n]/',$contact)) respond(422,false);
$isEmail=filter_var($contact,FILTER_VALIDATE_EMAIL)!==false;
$phone=preg_replace('/[\s()+.\-]/u','',$contact);
$isPhone=is_string($phone) && preg_match('/^[0-9٠-٩۰-۹]{7,20}$/u',$phone)===1;
if (!$isEmail && !$isPhone) respond(422,false);
$body="New website inquiry\n\nContact: ".$contact."\n\nProject description:\n".$message."\n";
$headers=['Content-Type: text/plain; charset=UTF-8'];
if ($isEmail) $headers[]='Reply-To: '.$contact;
$sent=mail('Head.Office@elitevision-sa.com','New ELITE website inquiry',$body,implode("\r\n",$headers));
respond($sent?200:503,$sent);
