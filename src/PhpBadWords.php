<?php
namespace Expalmer\PhpBadWords;

class PhpBadWords
{

    private $dictionary = [
        "anal",
        "ass",
        ["asshole", "among"],
        "butt"
    ];

    private $text;
    private $textOriginal;
    private $dictionaryWords = [];

    public function __construct()
    {
        $this->fillDictionaryWords();
    }

    /**
     * Fill the variable with only the words of the dictionary, without the original word rules
     *
     * @return void
     */
    private function fillDictionaryWords()
    {
        foreach ($this->dictionary as $w):
            $this->dictionaryWords[] = is_array($w) ? $w[0] : $w;
        endforeach;
    }

    /**
     * Set the bad words list from an array
     *
     * @param  array
     *
     * @return this
     * @throws \Exception
     */
    public function setDictionaryFromArray($array)
    {
        if (is_array($array)) {
            $this->dictionary = $array;
            $this->fillDictionaryWords();

            return $this;
        }
        throw new \Exception('Invalid dictionary, try to send an array or a file path!');
    }

    /**
     * Set the bad words list from a file
     *
     * @param  string
     *
     * @return this
     * @throws \Exception
     */
    public function setDictionaryFromFile($path)
    {
        if (file_exists($path)) {
            $dict = include $path;
            if (is_array($dict)) {
                $this->dictionary = $dict;
                $this->fillDictionaryWords();

                return $this;
            }
            throw new \Exception('The file content must be an array!');
        }
        throw new \Exception('File not found in ' . $path);
    }

    /**
     * Set the text to be checked
     *
     * @param string
     *
     * @return this
     */
    public function setText($text)
    {
        $this->textOriginal = $text;

        $isRtl     = $this->isRtl($text);
        $isCyrillic = $this->isCyrillic($text);

        if ($isRtl OR $isCyrillic) {
            $this->text = preg_replace('/\s\s+/', ' ', $text);
        } else {
            $this->text = preg_replace("/([^\w ]*)/i", "", $text);
        }

        return $this;
    }

    /**
     * Checks for bad words in the text but verifies each dictionary word rule,
     * like alone ou among each word in the text.
     *
     * @return boolean
     */
    public function check()
    {
        foreach ($this->dictionary as $word){
            $rule = "alone";
            if (is_array($word)) {
                $rule = isset($word[1]) ? $word[1] : $rule;
                $word = $word[0];
            }

            $isRtl     = $this->isRtl($word);
            $isCyrillic = $this->isCyrillic($word);

            if ($rule === "among" OR $isRtl OR $isCyrillic) {
                if (preg_match("/(" . $word . ")/i", $this->text)) {

                    return true;
                }
            } else {
                if (preg_match("/(\b)+(" . $word . ")+(\b)/i", $this->text)) {

                    return true;
                }
            }
        }

        //var_dump("end " . $word) ."\r\n";
        return false;
    }

    /**
     * Checks if the text has a bad word among each word
     *
     * @return boolean
     */
    public function checkAmong()
    {
        return !!preg_match("/(" . join("|", $this->dictionaryWords) . ")/i", $this->text);
    }

    /**
     * Checks if the text has a bad word exactly how it appears in the dictionary
     *
     * @return boolean
     */
    public function checkAlone()
    {
        return !!preg_match("/(\b)+(" . join("|", $this->dictionaryWords) . ")+(\b)/i", $this->text);
    }

    /**
     * Checks if string is right to left
     *
     * @param $string
     *
     * @return int
     */
    private function isRtl($string)
    {
        $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';

        return preg_match($rtl_chars_pattern, $string);
    }

    /**
     * Checks if string is cyrillic
     *
     * @param $string
     *
     * @return int
     */
    private function isCyrillic($string)
    {
        return preg_match('/[А-Яа-яЁё]/u', $string);
    }
}
