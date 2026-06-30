<?php
// contacto.php — Visaovale — Formulário de Contacto
// Coloque este ficheiro na mesma pasta que o index.html no servidor

header('Content-Type: text/plain; charset=utf-8');

// ── Configuração ──────────────────────────────────────
$para      = 'geral@visaovale.pt';          // EDITAR: email destino
$de_nome   = 'Website Visaovale';
$de_email  = 'noreply@visaovale.pt';        // EDITAR: deve existir no servidor
// ─────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'erro';
    exit;
}

// Sanitizar entradas
function limpar($val) {
    return htmlspecialchars(trim(strip_tags($val)), ENT_QUOTES, 'UTF-8');
}

$nome  = limpar($_POST['nome']  ?? '');
$email = limpar($_POST['email'] ?? '');
$tel   = limpar($_POST['tel']   ?? '');
$tipo  = limpar($_POST['tipo']  ?? '');
$local = limpar($_POST['local'] ?? '');
$msg   = limpar($_POST['msg']   ?? '');

// Validação mínima
if (empty($nome) || empty($email) || empty($msg)) {
    echo 'erro';
    exit;
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo 'erro';
    exit;
}

// Construir email
$assunto = "Contacto pelo site — {$tipo} — {$nome}";

$corpo  = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$corpo .= "  NOVO CONTACTO — VISAOVALE.PT\n";
$corpo .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$corpo .= "Nome:              {$nome}\n";
$corpo .= "Email:             {$email}\n";
$corpo .= "Telefone:          {$tel}\n";
$corpo .= "Tipo de Projecto:  {$tipo}\n";
$corpo .= "Localização:       {$local}\n\n";
$corpo .= "Mensagem:\n";
$corpo .= "─────────────────────────────────────────\n";
$corpo .= "{$msg}\n\n";
$corpo .= "─────────────────────────────────────────\n";
$corpo .= "Enviado em: " . date('d/m/Y H:i') . " (UTC)\n";
$corpo .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido') . "\n";

$headers  = "From: {$de_nome} <{$de_email}>\r\n";
$headers .= "Reply-To: {$nome} <{$email}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$enviado = mail($para, $assunto, $corpo, $headers);

echo $enviado ? 'ok' : 'erro';
?>
