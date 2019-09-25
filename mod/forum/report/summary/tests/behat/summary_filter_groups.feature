@mod @mod_forum @forumreport @forumreport_summary @javascript
Feature: Groups report filter is available if groups exist
  In order to retrieve targeted forum data
  As a teacher
  I can filter the forum summary report by groups of users

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | teacher1 | C2     | editingteacher |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group A | C1     | G1       |
      | Group B | C1     | G2       |
      | Group C | C1     | G3       |
      | Group D | C1     | G4       |
      | Group E | C2     | G5       |
    And the following "group members" exist:
      | user     | group |
      | teacher1 | G1    |
      | teacher1 | G4    |
      | teacher1 | G5    |
      | student1 | G3    |
    And the following "activities" exist:
      | activity | name   | description     | course | idnumber |
      | forum    | forum1 | C1 first forum  | C1     | forum1   |
      | forum    | forum2 | C1 second forum | C1     | forum2   |
      | forum    | forum1 | C2 first forum  | C2     | forum1   |
    And the following forum discussions exist in course "Course 1":
      | user     | forum  | name        | message    | created           |
      | teacher1 | forum1 | discussion1 | D1 message | ## 1 month ago ## |
      | teacher1 | forum1 | discussion2 | D2 message | ## 1 week ago ##  |
      | teacher1 | forum2 | discussion3 | D3 message | ## 4 days ago ##  |
      | student1 | forum1 | discussion4 | D4 message | ## 3 days ago ##  |
      | student2 | forum2 | discussion5 | D5 message | ## 2 days ago##   |
    And the following forum replies exist in course "Course 1":
      | user     | forum  | discussion  | message    | created           |
      | teacher1 | forum1 | discussion1 | D1 reply   | ## 3 weeks ago ## |
      | teacher1 | forum1 | discussion2 | D2 reply   | ## 6 days ago ##  |
      | teacher1 | forum2 | discussion3 | D3 reply   | ## 3 days ago ##  |
      | student1 | forum1 | discussion1 | D1 reply 2 | ## 2 weeks ago ## |
      | student2 | forum2 | discussion3 | D3 reply   | ## 2 days ago ##  |
    And the following forum discussions exist in course "Course 2":
      | user     | forum  | name        | message         | created          |
      | teacher1 | forum1 | discussion1 | D1 other course | ## 1 week ago ## |
      | teacher1 | forum1 | discussion2 | D2 other course | ## 4 days ago ## |

  Scenario: All groups can be selected or cleared together in the groups filter, and are selected by default
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I navigate to "Summary report" in current page administration
    And I wait until the page is ready
    Then "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I click on "Groups (all)" "button"
    And "Group A" "checkbox" should exist
    And "Group B" "checkbox" should exist
    And "Group C" "checkbox" should exist
    And "Group D" "checkbox" should exist
    And "Group E" "checkbox" should not exist
    And the following fields match these values:
      | Group A   | 1 |
      | Group B   | 1 |
      | Group C   | 1 |
      | Group D   | 1 |
      | No groups | 1 |
    And I click on "Clear" "link"
    And the following fields match these values:
      | Group A   | 0 |
      | Group B   | 0 |
      | Group C   | 0 |
      | Group D   | 0 |
      | No groups | 0 |
    And I click on "Select all" "link"
    And the following fields match these values:
      | Group A   | 1 |
      | Group B   | 1 |
      | Group C   | 1 |
      | Group D   | 1 |
      | No groups | 1 |
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"

  Scenario: The summary report can be filtered by a subset of groups, and re-ordering the results retains the filter
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I navigate to "Summary report" in current page administration
    And I wait until the page is ready
    Then "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    # Ensure users in no groups do not appear.
    And I click on "Groups (all)" "button"
    And I set the following fields to these values:
      | Group A   | 1 |
      | Group B   | 0 |
      | Group C   | 1 |
      | Group D   | 1 |
      | No groups | 0 |
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (3)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And I should not see "Student 2"
    # Ensure re-ordering retains filter.
    And I click on "Number of discussions posted" "link"
    And I wait until the page is ready
    And "Groups (3)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And I should not see "Student 2"
    # Ensure users in a deselected group do not appear.
    And I click on "Groups (all)" "button"
    And I set the following fields to these values:
      | Group A   | 0 |
      | Group B   | 0 |
      | Group C   | 1 |
      | Group D   | 0 |
      | No groups | 0 |
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (1)" "button" should exist
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And I should not see "Teacher 1"
    And I should not see "Student 2"

  Scenario: The summary report can be filtered as a mixture of users who are in no groups, or who are in a subset of groups
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I navigate to "Summary report" in current page administration
    And I wait until the page is ready
    Then "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I click on "Groups (all)" "button"
    And I set the following fields to these values:
      | Group A   | 1 |
      | Group B   | 0 |
      | Group C   | 0 |
      | Group D   | 1 |
      | No groups | 1 |
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (3)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I should not see "Student 1"

  Scenario: The summary report can be filtered by users who are in no groups only
    # Log in as admin so Teacher 1 not existing on page can be confirmed.
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I navigate to "Summary report" in current page administration
    And I wait until the page is ready
    Then "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I click on "Groups (all)" "button"
    And I set the following fields to these values:
      | Group A   | 0 |
      | Group B   | 0 |
      | Group C   | 0 |
      | Group D   | 0 |
      | No groups | 1 |
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (1)" "button" should exist
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I should not see "Teacher 1"
    And I should not see "Student 1"

  Scenario: Filtering by a group containing no users still allows the page to render
    # Log in as admin so Teacher 1 not existing on page can be confirmed.
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I navigate to "Summary report" in current page administration
    And I wait until the page is ready
    Then "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I click on "Groups (all)" "button"
    And I click on "Clear" "link"
    And I set the field "Group B" to "1"
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (1)" "button" should exist
    And I should see "Nothing to display"
    And I should not see "Teacher 1"
    And I should not see "Student 1"
    And I should not see "Student 2"

 Scenario: Selecting no groups in the groups filter still allows the page to render
    # Log in as admin so Teacher 1 not existing on page can be confirmed.
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "forum1"
    And I navigate to "Summary report" in current page administration
    And I wait until the page is ready
    Then "Groups (all)" "button" should exist
    And "Teacher 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "2"
    And "Student 1" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "1"
    And "Student 2" row "Number of discussions posted" column of "forumreport_summary_table" table should contain "0"
    And I click on "Groups (all)" "button"
    And I click on "Clear" "link"
    And I click on "Save" "link"
    And I wait until the page is ready
    And "Groups (0)" "button" should exist
    And I should see "Nothing to display"
    And I should not see "Teacher 1"
    And I should not see "Student 1"
    And I should not see "Student 2"
