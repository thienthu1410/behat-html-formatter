BehatHtmlFormatterPlugin
========================

Suggestions are more than welcome !

This is a behat 3 extension to generate HTML reports from your test results.

Add this to your behat.yml file:

<pre>
formatters:
    html:
        output_path: %paths.base%/build/html/behat
  extensions:
    thienthu1410\BehatHTMLFormatter\BehatHTMLFormatterExtension:
        name: html
        renderer: Twig
        not_count_tag: suite-setup,suite-teardown
        file_name: Index
        print_args: true        
</pre>

The *output* parameter is relative to %paths.base% and, when omitted, will default to that same path.

The *renderer* is the renderer engine and the report format that you want to be generated.

The *file_name* is optional. When it is added, the report name will be fixed instead fo generated, and this file will be overwritten with every build.

The *not_count_tag* is optional. When it is added, the scenarios having the tag defined in *not_count_tag* will not be counted, but still be printed in detail. Apply for scenarios as suite-setup, or suite-teardown

Actually, there is 1 format :

- **Twig** : new report format based on Twig, **requires Twig installed**

You must specify the format that you want to use in the *renderer* parameter.

File names have this format : *"renderer name"*_*"date hour"*

**Twig renderer only parameters:**

The *print_args* is optional. When it is added, the report will contain the arguments for each step if exists. (e.g. Tables)

The *print_outp* is optional. When it is added, the report will contain the output of each step if exists. (e.g. Exceptions)

To be done:
========================

1. Add parameters for behat.yml file
2. Add bootstrap as dependency
3. clean up html report
4. Add out parameter

Screenshots
=========================

Twig :
<img src="/images/reports_twig.PNG"></img>

Analysis when viewing report:
========================

<img src="/images/overview_charts.PNG"></img>
1. Overview charts
   - Feature charts: total number of features, how many features passed, how many features failed
   - Test Case charts: total number of test cases, how many test cases passed, how many test cases failed. Each test case is mapped with a scenario description in suite.
   - Scenario charts: total number of scenarios, how many scenarios passed, how many scenarios failed. Each scenario is mapped with a single scenario or and example in scenario outline
   - Examples about test cases and scenarios
     - Example 1: 1 test case, 1 scenario
      <pre>
      Scenario: test 1
          Given I am on "aaa"
          Then the url should match "aaa"         
      </pre>
     - Example 2: 1 test case, 2 scenarios
      <pre>
      Scenario Outline: test 1
        Given I am on "&lt;pagel&gt;"
        Then the url should match "&lt;url&gt;"
        Examples:
          | page | url |
          | aaa  | aaa |        
          | aaa  | bbb |
      </pre>    
     - Example 3: 2 test cases, 2 scenarios
       <pre>
       Scenario: test 1
           Given I am on "aaa"
           Then the url should match "aaa"
       Scenario: test 2
            Given I am on "bbb"
            Then the url should match "bbb"                
       </pre>    
     - Example 4: 2 test cases, 3 scenarios
       <pre>
       Scenario: test 1
           Given I am on "aaa"
           Then the url should match "aaa"
       Scenario Outline: test 2
           Given I am on "&lt;page&gt;"
           Then the url should match "&lt;url&gt;"
           Examples:
             | page | url |
             | aaa  | aaa |        
             | aaa  | bbb |               
       </pre>    
2. Suite overview
<img src="/images/overview_suite.PNG"></img>
 - Contains all features run in the suite.
    - At the top of each feature, there is info about the feature description
    - At the bottom of each feature, there is info about how many test  cases of that feature passed/failed. The number of failed test cases is in the red region, and the number of passed test cases is in the green region. There is also info about the feature's tags

3. Feature details    
   - Click the feature in the suite overview above to view a feature in detail
<img src="/images/feature_details.PNG"></img>    
   - Contains:
      - The feature overview (feature description, feature's tag)
      - The test cases of the features. Each test case contains info about the total number of scenarios, how many scenarios of test case passed, how many scenarios of test case failed, test case description, tags of test case. Click the scenario to view scenario details (includes scenario description, scenario's tags, all steps of scenario/passed step, failed step/reason why failed)
      - If the test case is not counted (has tags defined as *not_count_tag* in behat.yml), it will not have the info number of scenarios in total/passed scenarios/failed scenarios
      - Between each test case is a line break to separate them
