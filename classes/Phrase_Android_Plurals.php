<?php

require_once('Phrase_Android.php');

class Phrase_Android_Plurals extends Phrase_Android {

    const QUANTITY_ZERO = 'zero';
    const QUANTITY_ONE = 'one';
    const QUANTITY_TWO = 'two';
    const QUANTITY_FEW = 'few';
    const QUANTITY_MANY = 'many';
    const QUANTITY_OTHER = 'other';

    protected static $list = array(
        self::QUANTITY_ZERO,
        self::QUANTITY_ONE,
        self::QUANTITY_TWO,
        self::QUANTITY_FEW,
        self::QUANTITY_MANY,
        self::QUANTITY_OTHER
    );
    protected $values;

    public function __construct($id, $phraseKey, $enabledForTranslation = true) {
        parent::__construct($id, $phraseKey, $enabledForTranslation);
        $this->values = array(
            self::QUANTITY_ZERO => '',
            self::QUANTITY_ONE => '',
            self::QUANTITY_TWO => '',
            self::QUANTITY_FEW => '',
            self::QUANTITY_MANY => '',
            self::QUANTITY_OTHER => ''
        );
    }

    /**
     * Gets the phrase's payload in form of JSON data
     *
     * @return string payload as JSON data
     */
    public function getPayload() {
        return self::getPayloadFromValue($this->values);
    }

    public static function getList() {
        return self::$list;
    }

    /**
     * The default value to use for any quantities that have not been explicitly set
     *
     * @param string $defaultValue
     */
    public function fillWithDefault($defaultValue) {
        foreach (self::$list as $quantity) {
            if (empty($this->values[$quantity])) {
                $this->values[$quantity] = $defaultValue;
            }
        }
    }

    /**
     * Creates JSON payload from a phrase's value(s)
     *
     * @param mixed $value single string or array of strings (value for the phrase)
     * @return string JSON payload
     */
    public static function getPayloadFromValue($value) {
        $data = array(
            'class' => 'Phrase_Android_Plurals',
            'values' => $value
        );
        return json_encode($data);
    }

    /**
     * Sets the phrase's payload from the given JSON data
     *
     * @param string $json JSON data to get the payload from
     * @param bool $createKeysOnly whether the complete phrase should be created (true) or keys only (false)
     * @param bool $isUsingDefaultPhrase whether this is only using the default language's value and must thus be marked as empty
     */
    public function setPayload($json, $createKeysOnly = false, $isUsingDefaultPhrase = false) {
        $data = json_decode($json, true);
        if (!$createKeysOnly) {
            // CHECK WHETHER THIS PHRASE IS EMPTY OR NOT BEGIN
            $hasValues = false;
            if (!$isUsingDefaultPhrase && isset($data['values']) && is_array($data['values'])) {
                foreach ($data['values'] as $value) {
                    if (!empty($value)) {
                        $hasValues = true;
                    }
                }
            }
            $this->isEmpty = !$hasValues;
            // CHECK WHETHER THIS PHRASE IS EMPTY OR NOT END
            $this->values = $data['values'];
        }
        else {
            $this->isEmpty = true;
            $this->values = array();
            $keys = array_keys($data['values']);
            foreach ($keys as $key) {
                $this->values[$key] = '';
            }
        }
    }

    /**
     * Returns the list of values for this phrase
     *
     * @return array list of values
     */
    public function getPhraseValues() {
        return $this->values;
    }

    /**
     * Set the value at the given sub-key for this phrase
     *
     * @param string $subKey sub-key
     * @param string $value the new value to set
     * @throws Exception if the sub-key could not be found
     */
    public function setPhraseValue($subKey, $value) {
        if (isset($this->values[$subKey])) {
            $this->values[$subKey] = $value;
        }
        else {
            throw new Exception('Unknown sub-key '.$subKey);
        }
    }

    /**
     * Adds a new value to the given phrase object, either with the given sub-key or with an auto-incrementing ID
     *
     * @param string $value the value (phrase content) to add
     * @param string $subKey (optional) sub-key if no auto-incrementing ID can/should be used
     * @throws Exception (optionally) if this phrase object does not support auto-incrementing IDs and the given sub-key is not allowed
     */
    public function addValue($value, $subKey = NULL) {
        if (isset($this->values[$subKey])) {
            $this->values[$subKey] = $value;
        }
        else {
            throw new Exception('Unknown quantity ID: '.$subKey);
        }
    }

    /**
     * Returns the the number of complete values and total values for this phrase
     *
     * @return array the first entry contains the number of complete values in this phrase and the second the total number of values
     */
    public function getCompleteness() {
        $complete = 0;
        $total = 0;
        foreach ($this->values as $value) {
            if (!empty($value)) {
                $complete++;
            }
            $total++;
        }
        return array($complete, $total);
    }

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in Android XML format
     *
     * @return string output of this phrase
     */
    public function outputAndroidXML() {
        $valueEntries = array();
        foreach ($this->values as $quantity => $value) {
            $valueEntries[] = "\t\t".'<item quantity="'.$quantity.'">'.self::writeToRaw($value, File_IO::FORMAT_ANDROID_XML).'</item>';
        }

        return "\t".'<plurals name="'.$this->phraseKey.'">'. "\n" . implode("\n", $valueEntries) . "\n"."\t".'</plurals>';
    }

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in Android XML format with escaped HTML
     *
     * @return string output of this phrase
     */
    public function outputAndroidXMLEscapedHTML() {
        $valueEntries = array();
        foreach ($this->values as $quantity => $value) {
            $valueEntries[] = "\t\t".'<item quantity="'.$quantity.'">'.self::writeToRaw($value, File_IO::FORMAT_ANDROID_XML_ESCAPED_HTML).'</item>';
        }

        return "\t".'<plurals name="'.$this->phraseKey.'">'. "\n" . implode("\n", $valueEntries) . "\n"."\t".'</plurals>';
    }

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in JSON format
     *
     * @return string output of this phrase
     */
    public function outputJSON() {
        $valueEntries = array();
        foreach ($this->values as $quantity => $value) {
            $valueEntries[] = "\t\t".'"'.$quantity.'" : "'.self::writeToRaw($value, File_IO::FORMAT_JSON).'"';
        }

        return "\t".'"'.$this->phraseKey.'" : { "type" : "plurals", "content" : {'. "\n" . implode(",\n", $valueEntries) . "\n"."\t".'} }';
    }

    /**
     * Returns the output of this phrase for the specific platform and type of phrase in plaintext format
     *
     * @return string output of this phrase
     */
    public function outputPlaintext() {
        $valueEntries = array();
        foreach ($this->values as $quantity => $value) {
            $valueEntries[] = $this->phraseKey.';plurals;'.$quantity.';'.self::writeToRaw($value, File_IO::FORMAT_PLAINTEXT);
        }

        return implode("\n", $valueEntries);
    }

}

?>