<?php

namespace thienthu1410\BehatHTMLFormatter\Formatter;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Output\Exception\BadOutputPathException;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResults;
use thienthu1410\BehatHTMLFormatter\Classes\Feature;
use thienthu1410\BehatHTMLFormatter\Classes\Scenario;
use thienthu1410\BehatHTMLFormatter\Classes\Step;
use thienthu1410\BehatHTMLFormatter\Classes\Suite;
use thienthu1410\BehatHTMLFormatter\Classes\Testcase;
use thienthu1410\BehatHTMLFormatter\Printer\FileOutputPrinter;
use thienthu1410\BehatHTMLFormatter\Renderer\BaseRenderer;


/**
 * Class BehatHTMLFormatter
 * @package tests\features\formatter
 */
class BehatHTMLFormatter implements Formatter {

  //<editor-fold desc="Variables">
  /**
   * @var array
   */
  private $parameters;

  /**
   * @var
   */
  private $name;

  /**
   * @var
   */
  private $timer;

  /**
   * @var
   */
  private $memory;


  /**
   * @param String $outputPath where to save the generated report file
   */
  private $outputPath;

  /**
   * @param String $base_path Behat base path
   */
  private $base_path;

  /**
   * Printer used by this Formatter
   * @param $printer OutputPrinter
   */
  private $printer;

  /**
   * Renderer used by this Formatter
   * @param $renderer BaseRenderer
   */
  private $renderer;

  /**
   * Flag used by this Formatter
   * @param $print_args boolean
   */
  private $print_args;

  /**
   * Flag used by this Formatter
   * @param $print_outp boolean
   */
  private $print_outp;

  /**
   * Flag used by this Formatter
   * @param $loop_break boolean
   */
  private $loop_break;

  /**
   * Flag used by this Formatter
   * @param $loop_break boolean
   */
  private $not_count_tag;

  /**
   * @var Array
   */
  private $suites;

  /**
   * @var Suite
   */
  private $currentSuite;

  /**
   * @var int
   */
  private $featureCounter = 1;
  /**
   * @var Feature
   */
  private $currentFeature;

  /**
   * @var Testcase
   */
  private $currentTestcase;
  /**
   * @var int
   */
  private $testcaseCounter = 0;

  /**
   * @var Scenario
   */
  private $currentScenario;

  /**
   * @var Scenario[]
   */
  private $currentScenarios;
  /**
   * @var int
   */
  private $totalSteps = 0;
  /**
   * @var int
   */
  private $indexScenario = 0;
  /**
   * @var int
   */
  private $stepCounter = 0;

  /**
   * @var Scenario[]
   */
  private $failedScenarios;
  /**
   * @var Scenario[]
   */
  private $failedUnCountScenarios = [];

  /**
   * @var Scenario[]
   */
  private $passedScenarios;
  /**
   * @var Scenario[]
   */
  private $passedUnCountedScenarios = [];

  /**
   * @var Testcase[]
   */
  private $failedTestcases;
  /**
   * @var Testcase[]
   */
  private $failedUnCountedTestcases = [];

  /**
   * @var Testcase[]
   */
  private $passedTestcases;

  /**
   * @var Testcase[]
   */
  private $passedUncountedTestcases = [];

  /**
   * @var Feature[]
   */
  private $failedFeatures;

  /**
   * @var Feature[]
   */
  private $passedFeatures;

  /**
   * @var Step[]
   */
  private $failedSteps;

  /**
   * @var Step[]
   */
  private $passedSteps;

  /**
   * @var Step[]
   */
  private $pendingSteps;

  /**
   * @var Step[]
   */
  private $skippedSteps;
  //</editor-fold>

  //<editor-fold desc="Formatter functions">
  /**
   * @param $name
   * @param $base_path
   */
  function __construct($name, $renderer, $filename, $print_args, $print_outp, $loop_break, $notCountTag, $base_path) {
    $this->name = $name;
    $this->print_args = $print_args;
    $this->print_outp = $print_outp;
    $this->loop_break = $loop_break;
    $this->not_count_tag = $notCountTag;
    $this->renderer = new BaseRenderer($renderer, $base_path);
    $this->printer = new FileOutputPrinter($this->renderer->getNameList(), $filename, $base_path);
    $this->timer = new Timer();
    $this->memory = new Memory();
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * @return array The event names to listen to
   */
  public static function getSubscribedEvents() {
    return array(
      'tester.exercise_completed.before' => 'onBeforeExercise',
      'tester.exercise_completed.after' => 'onAfterExercise',
      'tester.suite_tested.before' => 'onBeforeSuiteTested',
      'tester.suite_tested.after' => 'onAfterSuiteTested',
      'tester.feature_tested.before' => 'onBeforeFeatureTested',
      'tester.feature_tested.after' => 'onAfterFeatureTested',
      'tester.scenario_tested.before' => 'onBeforeScenarioTested',
      'tester.scenario_tested.after' => 'onAfterScenarioTested',
      'tester.outline_tested.before' => 'onBeforeOutlineTested',
      'tester.outline_tested.after' => 'onAfterOutlineTested',
      'tester.step_tested.after' => 'onAfterStepTested',
    );
  }

  /**
   * Returns formatter name.
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Returns formatter description.
   *
   * @return string
   */
  public function getDescription() {
    return "Formatter for teamcity";
  }

  /**
   * Returns formatter output printer.
   *
   * @return OutputPrinter
   */
  public function getOutputPrinter() {
    return $this->printer;
  }

  /**
   * Sets formatter parameter.
   *
   * @param string $name
   * @param mixed $value
   */
  public function setParameter($name, $value) {
    $this->parameters[$name] = $value;
  }

  /**
   * Returns parameter name.
   *
   * @param string $name
   * @return mixed
   */
  public function getParameter($name) {
    return $this->parameters[$name];
  }

  /**
   * Verify that the specified output path exists or can be created,
   * then sets the output path.
   *
   * @param String $path Output path relative to %paths.base%
   * @throws BadOutputPathException
   */
  public function setOutputPath($path) {
    $outpath = realpath($this->base_path . DIRECTORY_SEPARATOR . $path);
    if (!file_exists($outpath)) {
      if (!mkdir($outpath, 0755, TRUE)) {
        throw new BadOutputPathException(
          sprintf(
            'Output path %s does not exist and could not be created!',
            $outpath
          ),
          $outpath
        );
      }
    }
    else {
      if (!is_dir($outpath)) {
        throw new BadOutputPathException(
          sprintf(
            'The argument to `output` is expected to the a directory, but got %s!',
            $outpath
          ),
          $outpath
        );
      }
    }
    $this->outputPath = $outpath;
  }

  /**
   * Returns output path
   *
   * @return String output path
   */
  public function getOutputPath() {
    return $this->outputPath;
  }

  /**
   * Returns if it should print the step arguments
   *
   * @return boolean
   */
  public function getPrintArguments() {
    return $this->print_args;
  }

  /**
   * Returns if it should print the step outputs
   *
   * @return boolean
   */
  public function getPrintOutputs() {
      return $this->print_outp;
  }

  /**
   * Returns if it should print scenario loop break
   *
   * @return boolean
   */
  public function getPrintLoopBreak() {
      return $this->loop_break;
  }

  public function getTimer() {
    return $this->timer;
  }

  public function getMemory() {
    return $this->memory;
  }

  public function getSuites() {
    return $this->suites;
  }

  public function getCurrentSuite() {
    return $this->currentSuite;
  }

  public function getFeatureCounter() {
    return $this->featureCounter;
  }

  public function getCurrentFeature() {
    return $this->currentFeature;
  }

  public function getCurrentScenario() {
    return $this->currentScenario;
  }

  public function getFailedScenarios() {
    return $this->failedScenarios;
  }

  public function getPassedScenarios() {
    return $this->passedScenarios;
  }

  public function getFailedUncountedScenarios() {
      return $this->failedUnCountScenarios;
  }

  public function getPassedUncountedScenarios() {
      return $this->passedUnCountedScenarios;
  }

  public function getCurrentTestcase() {
      return $this->currentTestcase;
  }

  public function getFailedTestcases() {
      return $this->failedTestcases;
  }

  public function getPassedTestcases() {
      return $this->passedTestcases;
  }

  public function getFailedUncountedTestcases() {
      return $this->failedUnCountedTestcases;
  }

  public function getPassedUncountedTestcases() {
      return $this->passedUncountedTestcases;
  }

  public function getFailedFeatures() {
    return $this->failedFeatures;
  }

  public function getPassedFeatures() {
    return $this->passedFeatures;
  }

  public function getFailedSteps() {
    return $this->failedSteps;
  }

  public function getPassedSteps() {
    return $this->passedSteps;
  }

  public function getPendingSteps() {
    return $this->pendingSteps;
  }

  public function getSkippedSteps() {
    return $this->skippedSteps;
  }


  //</editor-fold>

  //<editor-fold desc="Event functions">
  /**
   * @param BeforeExerciseCompleted $event
   */
  public function onBeforeExercise(BeforeExerciseCompleted $event) {
    $this->timer->start();

    $print = $this->renderer->renderBeforeExercise($this);
    $this->printer->write($print);
  }

  /**
   * @param AfterExerciseCompleted $event
   */
  public function onAfterExercise(AfterExerciseCompleted $event) {

    $this->timer->stop();

    $print = $this->renderer->renderAfterExercise($this);
    $this->printer->writeln($print);
  }

  /**
   * @param BeforeSuiteTested $event
   */
  public function onBeforeSuiteTested(BeforeSuiteTested $event) {
    $this->currentSuite = new Suite();
    $this->currentSuite->setName($event->getSuite()->getName());

    $print = $this->renderer->renderBeforeSuite($this);
    $this->printer->writeln($print);
  }

  /**
   * @param AfterSuiteTested $event
   */
  public function onAfterSuiteTested(AfterSuiteTested $event) {
    $this->suites[] = $this->currentSuite;

    $print = $this->renderer->renderAfterSuite($this);
    $this->printer->writeln($print);
  }

  /**
   * @param BeforeFeatureTested $event
   */
  public function onBeforeFeatureTested(BeforeFeatureTested $event) {
    $feature = new Feature();
    $feature->setId($this->featureCounter);
    $this->featureCounter++;
    $feature->setName($event->getFeature()->getTitle());
    $feature->setDescription($event->getFeature()->getDescription());
    $feature->setTags($event->getFeature()->getTags());
    $feature->setFile($event->getFeature()->getFile());
    $this->currentFeature = $feature;

    $print = $this->renderer->renderBeforeFeature($this);
    $this->printer->writeln($print);
  }

  /**
   * @param AfterFeatureTested $event
   */
  public function onAfterFeatureTested(AfterFeatureTested $event) {
    $this->currentSuite->addFeature($this->currentFeature);
    if ($this->currentFeature->allPassed()) {
      $this->passedFeatures[] = $this->currentFeature;
    }
    else {
      $this->failedFeatures[] = $this->currentFeature;
    }

    $print = $this->renderer->renderAfterFeature($this);
    $this->printer->writeln($print);
  }

  /**
   * @param BeforeScenarioTested $event
   */
  public function onBeforeScenarioTested(BeforeScenarioTested $event) {
    $this->currentScenarios = [];
    $this->stepCounter = 0;
    $this->totalSteps = 0;
    $this->indexScenario = 0;

    $testcase = new Testcase();
//    $testcase->setId($this->testcaseCounter);
    $this->testcaseCounter++;
    $testcase->setName($event->getScenario()->getTitle());
    $testcase->setTags($event->getScenario()->getTags());
    $this->currentTestcase = $testcase;

    $print = $this->renderer->renderBeforeTestcase($this);
    $this->printer->writeln($print);

    $scenario = new Scenario();
    $scenario->setName($event->getScenario()->getTitle());
    $scenario->setTags($event->getScenario()->getTags());
    $scenario->setLine($event->getScenario()->getLine());
    $this->currentScenario = $scenario;

    $print = $this->renderer->renderBeforeScenario($this);
    $this->printer->writeln($print);
  }

  /**
   * @param AfterScenarioTested $event
   */
  public function onAfterScenarioTested(AfterScenarioTested $event) {
    $scenarioPassed = $event->getTestResult()->isPassed();

      // Skipping report for NO_TESTS scenario
    if ($event->getTestResult()->getResultCode() === TestResults::NO_TESTS){
      return;
    }

    $isCount = true;

    foreach ($this->not_count_tag as $notCountTag) {
        $isCount = !$event->getScenario()->hasTag($notCountTag);
        if ($isCount === false){
            break;
        }
    }

    if ($scenarioPassed) {
      $this->passedScenarios[] = $this->currentScenario;

      if ($isCount === true) {
          $this->currentTestcase->addPassedScenario();
      }

      else {
          $this->passedUnCountedScenarios[] = $this->currentScenario;
          $this->passedUncountedTestcases[] = $this->currentTestcase;
      }

      $this->currentTestcase->setCount($isCount);
      $this->passedTestcases[] = $this->currentTestcase;

      if ($isCount === true) {
          $this->currentFeature->addPassedTestcase();
      }
    }
    else {
      $this->failedScenarios[] = $this->currentScenario;

      if ($isCount === true) {
          $this->currentTestcase->addFailedScenario();
      }

      else {
          $this->failedUnCountScenarios = $this->currentScenario;
          $this->failedUncountedTestcases = $this->currentTestcase;

      }

      $this->currentTestcase->setCount($isCount);
      $this->failedTestcases[] = $this->currentTestcase;

      if ($isCount === true) {
          $this->currentFeature->addFailedTestcase();
      }
    }

    $this->currentScenario->setLoopCount(1);
    $this->currentScenario->setPassed($scenarioPassed);
    $this->currentTestcase->addScenario($this->currentScenario);
    $this->currentTestcase->setPassed($scenarioPassed);
    $this->currentFeature->addTestcase($this->currentTestcase);


    $print = $this->renderer->renderAfterScenario($this);
    $this->printer->writeln($print);
  }

  /**
   * @param BeforeOutlineTested $event
   */
  public function onBeforeOutlineTested(BeforeOutlineTested $event) {
//    $scenario = new Scenario();
//    $scenario->setName($event->getOutline()->getTitle());
//    $scenario->setTags($event->getOutline()->getTags());
//    $scenario->setLine($event->getOutline()->getLine());
//    $this->currentScenario = $scenario;

      $testcase = new Testcase();
//      $testcase->setId($this->testcaseCounter);
      $this->testcaseCounter++;
      $testcase->setName($event->getOutline()->getTitle());
      $testcase->setTags($event->getOutline()->getTags());
      $this->currentTestcase = $testcase;

      $print = $this->renderer->renderBeforeTestcase($this);
      $this->printer->writeln($print);

      $this->totalSteps = sizeof($event->getOutline()->getSteps());
      $this->currentScenarios = [];
      $totalExamples = $event->getOutline()->getExamples();
      foreach ($totalExamples as $example){
        $scenario = new Scenario();
        $scenario->setName($event->getOutline()->getTitle());
        $scenario->setTags($event->getOutline()->getTags());
        $scenario->setLine($event->getOutline()->getLine());
        $this->currentScenario = $scenario;
        $this->currentScenarios[] = $this->currentScenario;
      }

      $print = $this->renderer->renderBeforeTestcase($this);
      $this->printer->writeln($print);

      $print = $this->renderer->renderBeforeTestcase($this);
      $this->printer->writeln($print);
  }

  /**
   * @param AfterOutlineTested $event
   */
  public function onAfterOutlineTested(AfterOutlineTested $event) {
//    $scenarioPassed = $event->getTestResult()->isPassed();
//
//    if ($scenarioPassed) {
//      $this->passedScenarios[] = $this->currentScenario;
//      $this->currentFeature->addPassedScenario();
//    }
//    else {
//      $this->failedScenarios[] = $this->currentScenario;
//      $this->currentFeature->addFailedScenario();
//    }
//
//    $this->currentScenario->setLoopCount(sizeof($event->getTestResult()));
//    $this->currentScenario->setPassed($event->getTestResult()->isPassed());
//    $this->currentFeature->addScenario($this->currentScenario);

    $index = 0;
    $testResults = $event->getTestResult();
    $testcasePassed = true;

    foreach ($testResults as $testResult){
        $scenarioPassed = $testResult->isPassed();
        $this->currentScenario = $this->currentScenarios[$index];

        if ($scenarioPassed) {
          $this->passedScenarios[] = $this->currentScenario;
          $this->currentTestcase->addPassedScenario();
        }
        else {
          $testcasePassed = false;
          $this->failedScenarios[] = $this->currentScenario;
          $this->currentTestcase->addFailedScenario();
        }

        $this->currentScenario->setLoopCount(1);
        $this->currentScenario->setPassed($scenarioPassed);
        $this->currentTestcase->addScenario($this->currentScenario);


        ++$index;
    }

    if ($testcasePassed){
      $this->passedTestcases[] = $this->currentTestcase;
      $this->currentFeature->addPassedTestcase();
     }

    else{
        $this->failedTestcases[] = $this->currentTestcase;
        $this->currentFeature->addFailedTestcase();
    }

    $this->currentTestcase->setPassed($testcasePassed);
    $this->currentTestcase->setCount(true);
    $this->currentFeature->addTestcase($this->currentTestcase);

    $print = $this->renderer->renderAfterTestcase($this);
    $this->printer->writeln($print);

    $print = $this->renderer->renderAfterOutline($this);
    $this->printer->writeln($print);

    $this->currentScenarios = [];
    $this->stepCounter = 0;
    $this->totalSteps = 0;
    $this->indexScenario = 0;

  }

  /**
   * @param BeforeStepTested $event
   */
  public function onBeforeStepTested(BeforeStepTested $event) {
    $print = $this->renderer->renderBeforeStep($this);
    $this->printer->writeln($print);
  }


  /**
   * @param AfterStepTested $event
   */
  public function onAfterStepTested(AfterStepTested $event) {
    $result = $event->getTestResult();

      //$this->dumpObj($event->getStep()->getArguments());
    /** @var Step $step */
    $step = new Step();
    $step->setKeyword($event->getStep()->getKeyword());
    $step->setText($event->getStep()->getText());
    $step->setLine($event->getStep()->getLine());
    $step->setArguments($event->getStep()->getArguments());
    $step->setResult($result);
    $step->setResultCode($result->getResultCode());

    //What is the result of this step ?
    if (is_a($result, 'Behat\Behat\Tester\Result\UndefinedStepResult')) {
      //pending step -> no definition to load
      $this->pendingSteps[] = $step;
    }
    else {
      if (is_a($result, 'Behat\Behat\Tester\Result\SkippedStepResult')) {
        //skipped step
        /** @var ExecutedStepResult $result */
        $step->setDefinition($result->getStepDefinition());
        $this->skippedSteps[] = $step;
      }
      else {
        //failed or passed
        if ($result instanceof ExecutedStepResult) {
          $step->setDefinition($result->getStepDefinition());
          $exception = $result->getException();
          if ($exception) {
            $step->setException($exception->getMessage());
            $this->failedSteps[] = $step;
          }
          else {
            $step->setOutput($result->getCallResult()->getStdOut());
            $this->passedSteps[] = $step;
          }
        }
      }
    }

//    check in case scenario outline
    if (!empty($this->currentScenarios)){
        $this->currentScenario = $this->currentScenarios[$this->indexScenario];

        $this->stepCounter++;
        if ($this->stepCounter === $this->totalSteps)
        {
            $this->indexScenario++;
            $this->stepCounter = 0;
        }

    }

    $this->currentScenario->addStep($step);

    $print = $this->renderer->renderAfterStep($this);
    $this->printer->writeln($print);
  }

  /**
   * @param $text
   */
  public function printText($text) {
    file_put_contents('php://stdout', $text);
    }

   /**
    * @param $obj
    */
  public function dumpObj($obj) {
    ob_start();
    var_dump($obj);
    $result = ob_get_clean();
    $this->printText($result);
  }

}
