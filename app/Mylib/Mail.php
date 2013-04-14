<?php

/*
 * наследование ради патча макс длины заголовка сообщения в 72 символа 
 */
class Mylib_Mail extends Zend_Mail
{
    /**
     * Encode header fields
     *
     * Encodes header content according to RFC1522 if it contains non-printable
     * characters.
     *
     * @param  string $value
     * @return string
     */
    protected function _encodeHeader($value)
    {
        if (Zend_Mime::isPrintable($value) === false) {
            if ($this->getHeaderEncoding() === Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
                $value = Zend_Mime::encodeQuotedPrintableHeader($value, $this->getCharset(), Zend_Mime::LINELENGTH, Zend_Mime::LINEEND);
            } else {
                $value = Zend_Mime::encodeBase64Header($value, $this->getCharset(), 1024, Zend_Mime::LINEEND);
            }
        }

        return $value;
    }
}