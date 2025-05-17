<?php

namespace App\Helpers;

use App\Models\KTRLOption;

class KTRLOptionsHelper
{
    /* The singleton instance */
    private static KTRLOptionsHelper $instance;

    /* The options */
    private KTRLOption $options;

    /**
     * Constructor method
     * The constructor method is protected, due to it's singleton
     */
    protected function __construct() {
        $this->options = KTRLOption::where('id', 1)->firstOrfail();
    }

    /**
     * Clone method
     * Should be disabled due to it's singleton requirement
     */
    protected function __clone() {}

    /**
     * Wakeup method
     * Should be disabled due to it's singleton requirement
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Method to get KTRLOptionsHelper instance
     * @return KTRLOptionsHelper the instance
     */
    public static function getInstance(): KTRLOptionsHelper
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function getKTRLVersion() : string {
        return $this->options->ktrl_version;
    }
}
