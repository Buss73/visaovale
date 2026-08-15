<?php
/* ─────────────────────────────────────────────────────────────
   VISÃOVALE · recepção do formulário de contacto
   Devolve exactamente "ok" em caso de sucesso (o JS espera isso).
   ───────────────────────────────────────────────────────────── */

declare(strict_types=1);
mb_internal_encoding('UTF-8');
header('Content-Type: text/plain; charset=UTF-8');

$DESTINO = 'geral@visaovale.com';
$REMETENTE = 'site@visaovale.com';   // tem de ser um endereço do próprio domínio

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('erro'); }

function campo(string $k, int $max = 500): string {
    $v = isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    $v = str_replace(["\r", "\n", "\0"], ' ', $v);        // anti header-injection
    return mb_substr($v, 0, $max);
}

$nome      = campo('nome', 120);
$email     = campo('email', 160);
$tel       = campo('tel', 40);
$objectivo = campo('objectivo', 60);
$terreno   = campo('terreno', 30);
$local     = campo('local', 120);
$area      = campo('area', 30);
$orcamento = campo('orcamento', 60);
$prazo     = campo('prazo', 60);
$msg       = mb_substr(trim((string) ($_POST['msg'] ?? '')), 0, 4000);

// honeypot opcional: se um bot preencher o campo escondido, fingimos sucesso
if (!empty($_POST['website'])) { exit('ok'); }

if ($nome === '' || $objectivo === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); exit('erro');
}

$linhas = [
    'Nome:               ' . $nome,
    'Email:              ' . $email,
    'Telefone:           ' . ($tel ?: '—'),
    '',
    'O que pretende:     ' . $objectivo,
    'Já tem terreno:     ' . ($terreno ?: '—'),
    'Localização:        ' . ($local ?: '—'),
    'Área aproximada:    ' . ($area ? $area . ' m²' : '—'),
    'Orçamento estimado: ' . ($orcamento ?: '—'),
    'Quando começa:      ' . ($prazo ?: '—'),
    '',
    'Mensagem:',
    ($msg ?: '—'),
    '',
    '───────────────────────────────',
    'Enviado em ' . date('Y-m-d H:i') . ' · IP ' . ($_SERVER['REMOTE_ADDR'] ?? '?'),
];
$corpo = implode("\n", $linhas);

$assunto = '=?UTF-8?B?' . base64_encode('Pedido de contacto · ' . $objectivo . ' · ' . $nome) . '?=';

$headers  = 'From: VISÃOVALE Site <' . $REMETENTE . ">\r\n";
$headers .= 'Reply-To: ' . $nome . ' <' . $email . ">\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "MIME-Version: 1.0\r\n";

// cópia local, útil se o mail() falhar no alojamento
@file_put_contents(__DIR__ . '/leads.log', $corpo . "\n\n", FILE_APPEND | LOCK_EX);

echo mail($DESTINO, $assunto, $corpo, $headers) ? 'ok' : 'erro';
