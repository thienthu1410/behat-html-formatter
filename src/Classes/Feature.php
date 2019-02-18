<?php
/**
 * Created by PhpStorm.
 * User: nealv
 * Date: 05/01/15
 * Time: 14:39
 */

namespace thienthu1410\BehatHTMLFormatter\Classes;


class Feature
{
    //<editor-fold desc="Variables">
    private $id;
    private $name;
    private $description;
    private $tags;
    private $file;
    private $failedTestcases = 0;
    private $passedTestcases = 0;
    private $testcaseCounter = 1;

    /**
     * @var Testcase[]
     */
    private $testcases;
    //</editor-fold>

    //<editor-fold desc="Getters/Setters">
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return Testcase[]
     */
    public function getTestcases()
    {
        return $this->testcases;
    }

    /**
     * @param Testcase[] $testcases
     */
    public function setTestcases($testcases)
    {
        $this->testcases = $testcases;
    }

    /**
     * @param $testcase Testcase
     */
    public function addTestcase($testcase)
    {
        $testcase->setId($this->testcaseCounter);
        $this->testcaseCounter++;
        $this->testcases[] = $testcase;
    }

    /**
     * @return mixed
     */
    public function getFailedTestcases()
    {
        return $this->failedTestcases;
    }

    /**
     * @param mixed $failedTestcases
     */
    public function setFailedTestcases($failedTestcases)
    {
        $this->failedTestcases = $failedTestcases;
    }

    public function addFailedTestcase($number = 1)
    {
        $this->failedTestcases++;
    }

    /**
     * @return mixed
     */
    public function getPassedTestcases()
    {
        return $this->passedTestcases;
    }

    /**
     * @param mixed $passedTestcases
     */
    public function setPassedTestcases($passedTestcases)
    {
        $this->passedTestcases = $passedTestcases;
    }

    public function addPassedTestcase($number = 1)
    {
        $this->passedTestcases++;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    //</editor-fold>

    //<editor-fold desc="Function">
    public function allPassed()
    {
        if ($this->failedTestcases == 0) {
            return true;
        }
        return false;
    }

    public function getPassedClass()
    {
        if ($this->allPassed()) {
            return "passed";
        }
        return "failed";
    }

    public function getPercentPassed()
    {
        return ($this->getPassedTestcases() / ($this->getTotalAmountOfTestcases())) * 100;
    }

    public function getPercentFailed()
    {
        return ($this->getFailedTestcases() / ($this->getTotalAmountOfTestcases())) * 100;
    }

    public function getTotalAmountOfTestcases()
    {
        return $this->getPassedTestcases() + $this->getFailedTestcases();
    }
    //</editor-fold>
}
