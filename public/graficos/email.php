<?php
$nome		= 'DTI - GAROPABA/SC';	
$alerta		= 'Chuva '.$volume.' mm';;	

// Variável que junta os valores acima e monta o corpo do email

$Vai = "Remetente: $nome\n\nAlerta: $alerta\n\n";

require_once("phpmailer/class.phpmailer.php");

define('GUSER', 'xxxx@xxxxxxx.sc.gov.br');	// <-- Insira aqui o seu GMail
define('GPWD', '202xxxx0.xxxx');		// <-- Insira aqui a senha do seu GMail

function smtpmailer($para, $de, $de_nome, $assunto, $corpo) { 
	global $error;

	$mail = new PHPMailer();
	$mail->IsSMTP();		// Ativar SMTP
	$mail->SMTPDebug = 0;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
	$mail->SMTPAuth = true;		// Autenticação ativada
	$mail->SMTPSecure = 'ssl';	// SSL REQUERIDO pelo GMail
	$mail->Host = 'smtp.xxxxxxx.org.br';	// SMTP utilizado
	$mail->Port = 465;  		// A porta 465 deverá estar aberta em seu servidor
	$mail->Username = GUSER;
	$mail->Password = GPWD;
	$mail->SetFrom($de, $de_nome);
	$mail->Subject = $assunto;
	$mail->Body = $corpo;
	$mail->AddAddress($para);
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		return false;
	} else {
		$error = '<br>OK!';
		return true;
	}
	
}
// Insira abaixo o email que irá receber a mensagem, o email que irá enviar (o mesmo da variável GUSER), 
// nome do email que envia a mensagem, o Assunto da mensagem e por último a variável com o corpo do email.

 if (smtpmailer('destino@gmail.com', 'xxxx@xxxxxxx.sc.gov.br', 'DTI - GAROPABA/SC', 'Alerta de Chuva', $Vai)) {

	echo "Email de alerta enviado!";

}
if (!empty($error)) echo $error;

?>