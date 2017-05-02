<?php
/**
* Envoie de Mail
*/
class Mail 
{
	private $_nom_expediteur;
	private $_mail_expediteur;
	private $_mail_replyto;
	private $_mails_destinataires;
	private $_mails_bcc;
	private $_objet;
	private $_texte;
	private $_html;
	private $_fichiers;
	private $_message;
	private $_frontiere;
	private $_headers;

	public static function _ValidateEmail ($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	public function __construct($mail_expediteur, $nom_expediteur, $mail_replyto)
	{
		if (!self::_ValidateEmail($mail_expediteur)) {
			throw new InvalidArgumentException("Error email expediteur");
		}
		if (!self::_ValidateEmail($mail_replyto)) {
			throw new InvalidArgumentException("Error email replyto");
		}

		$this->_nom_expediteur = $nom_expediteur;
		$this->_mail_expediteur = $mail_expediteur;
		$this->_mail_replyto = $mail_replyto;
		$this->_mails_destinataires = "";
		$this->_mails_bcc = "";
		$this->_objet = "";
		$this->_texte = "";
		$this->_html = "";
		$this->_fichiers = "";
		$this->_message = "";
		$this->_frontiere = md5(uniqid(mt_rand()));
		$this->_headers = "";
	}
	public function ajouter_destinataire($mail){
		if (!self::_ValidateEmail($mail)) {
			throw new InvalidArgumentException("Error email destinataire");
		}
		if ($this->_mails_destinataires == '') {
			$this->_mails_destinataires = $mail;
		}
		else{
			$this->_mails_destinataires .= ';'.$mail;
		}
	}
	public function ajouter_bcc($mail){
		if (!self::_ValidateEmail($mail)) {
			throw new InvalidArgumentException("Error bcc");
		}
		if ($this->_mails_bcc == "") {
			$this->_mails_bcc = $mail;
		}
		else {
			$this->_mails_bcc .= ";".$mail;
		}
	}
	public function ajouter_pj($fichier){
		if (!file_exists($fichier)) {
			throw new InvalidArgumentException("Piece jointe inexistante");
		}
		if ($this->_fichiers == "") { //mettre entre guillemet le chemain du fichier en partant de l'endroit ou se trouve le fichier de code
			$this->_fichiers = $fichier;
		}
		else {
			$this->_fichiers .= ";".$fichier;
		}
	}
	public function contenu($objet, $texte, $html){
		$this->_objet = $objet;
		$this->_texte = $texte;
		$this->_html = $html;
	}
	public function envoyer(){
		$this->_headers = 'From : "'.$this->_nom_expediteur.'" <'.$this->_mail_expediteur.'>'."\n";
		$this->_headers .= 'Return-Path : <'.$this->_mail_replyto.'>'."\n";
		$this->_headers .= 'MIME-Version : 1.0 '."\n";
		if ($this->_mails_bcc != "") {
			$this->_headers .= 'Bcc : '.$this->_mails_bcc."\n";
		}
		$this->_headers .= 'Content-Type: multipart/mixed; boundary="'.$this->_frontiere.'"';
		if ($this->_texte != "") {
			$this->_message .= '--'.$this->_frontiere."\n";
			$this->_message .= 'Content-Type: text/plain; charset="utf-8"'."\n";
			$this->_message .= 'Content-Transfer-Encoding: 8bits'."\n\n";
			$this->_message .= $this->_texte."\n\n";
		}
		if ($this->_html != "") {
			$this->_message .= '--'.$this->_frontiere."\n";
			$this->_message .= 'Content-Type: text/html; charset="utf-8"'."\n";
			$this->_message .= 'Content-Transfer-Encoding: 8bits'."\n\n";
			$this->_message .= $this->_html."\n\n";
		}
		if ($this->_fichiers != "") {
			$tab_fichiers = explode(";", $this->_fichiers);
			$nb_fichiers = count($tab_fichiers);
			for ($i=0; $i < $nb_fichiers; $i++) { 
				$this->_message .= '--'.$this->_frontiere."\n";
				// $this->_message .= 'Content-Type: image/jpeg; name="'.$tab_fichiers[$i].'"'."\n";
				$this->_message .= 'Content-Type: '.mime_content_type($tab_fichiers[$i]).'; name="'.$tab_fichiers[$i].'"'."\n";
				$this->_message .= 'Content-Transfer-Encoding: base64'."\n";
				$this->_message .= 'Content-Disposition:attachement; filename="'.$tab_fichiers[$i].'"'."\n\n";
				$this->_message .= chunk_split(base64_encode(file_get_contents($tab_fichiers[$i])))."\n\n";
			}
		}
		if (!mail($this->_mails_destinataires, $this->_objet, $this->_message, $this->_headers)) {
			throw new Exception("Envoie de mail echoué");
		} else {
			return true;
		}
	}
}