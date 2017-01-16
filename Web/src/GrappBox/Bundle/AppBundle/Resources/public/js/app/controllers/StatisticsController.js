/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP profile settings
app.controller("StatisticsController", ["$http", "notificationFactory", "$rootScope", "$route", "$scope",
    function($http, notificationFactory, $rootScope, $route, $scope) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { loaded: false, valid: false };
  $scope.statistics = {
    project_id: $route.current.params.project_id,
    issue: {
      total: "",
      assignation: { assigned: "", unassigned: "" },
      status: { open: "", closed: "" },
      byCustomer: "",
      evolution: "",
      repartition: {
        tag: { chart: {}, valid: true },
        user: { chart: {}, valid: true }
      }
    },
    customer: { actual: "", maximum: "" },
    project: { advancement: { chart: {}, valid: true }, dates: { begin: "", end: "" } },
    storage: { chart: {} },
    task: {
      current: "",
      done: "",
      late: "",
      total: "",
      repartition: { chart: {}, valid: true }
    },
    talk: { team: "", customer: "", total: "" },
    user: { advancement: { chart: {}, valid: true }, charge: { chart: {}, valid: true } }
  };
  $scope.error = {
    issue: {
      none: "Your project doesn't have any issue.",
      repartition: { tag: "None of your issues are assigned to a tag.", user: "None of your issues are assigned to a user." }
    },
    tag: "Your project's bugtracker doesn't have any tag.",
    task: "None of your tasks are assigned to a user.",
    user: "None of your tasks are assigned to a user."
  };



  /* ==================== GET PROJECT STATISTICS ==================== */

  var statisticsReceived = function(response) {
    if (response && response.data && response.data.info && response.data.info.return_code) {
      if (response.data.info.return_code == "1.16.1") {

        // Project dates (begin/end) (values)
        $scope.statistics.project.dates.begin = response.data.data.projectTimeLimits.projectStart;
        $scope.statistics.project.dates.end = response.data.data.projectTimeLimits.projectEnd;

        // Storage status (used/total ratio) (pie)
        $scope.statistics.storage.chart = {};
        $scope.statistics.storage.chart.label = ["Used", "Free"];
        $scope.statistics.storage.chart.data = [+response.data.data.storageSize.occupied / 1048576, (+response.data.data.storageSize.total - +response.data.data.storageSize.occupied) / 1048576];
        $scope.statistics.storage.chart.series = ["Space"];

        // Opened/closed issues and total (values)
        $scope.statistics.issue.status.open = response.data.data.openCloseBug.open;
        $scope.statistics.issue.status.closed = response.data.data.openCloseBug.closed;
        $scope.statistics.issue.total = +$scope.statistics.issue.status.open + +$scope.statistics.issue.status.closed;

        // Assigned/Unassigned issues (values)
        $scope.statistics.issue.assignation.assigned = response.data.data.bugAssignationTracker.assigned;
        $scope.statistics.issue.assignation.unassigned = response.data.data.bugAssignationTracker.unassigned;

        // Issue repartition by user (issues/user ratio) (pie)
        if (!response.data.data.bugsUsersRepartition || !response.data.data.bugsUsersRepartition.length)
          $scope.statistics.issue.repartition.user.valid = false;
        else {
          $scope.statistics.issue.repartition.user.chart = {};
          $scope.statistics.issue.repartition.user.chart.label = [];
          $scope.statistics.issue.repartition.user.chart.data = [];
          for (var i = 0; i < response.data.data.bugsUsersRepartition.length; ++i) {
            $scope.statistics.issue.repartition.user.chart.label.push(response.data.data.bugsUsersRepartition[i].user.firstname + " " + response.data.data.bugsUsersRepartition[i].user.lastname);
            $scope.statistics.issue.repartition.user.chart.data.push(response.data.data.bugsUsersRepartition[i].value);
          }
        }

        // Customer issues (value)
        $scope.statistics.issue.byCustomer = response.data.data.clientBugTracker;

        // Issue repartition by tag (issues/tags ratio) (pie)
        if (!response.data.data.bugsTagsRepartition || !response.data.data.bugsTagsRepartition.length)
          $scope.statistics.issue.repartition.tag.valid = false;
        else {
          $scope.statistics.issue.repartition.tag.chart = {};
          $scope.statistics.issue.repartition.tag.chart.label = [];
          $scope.statistics.issue.repartition.tag.chart.data = [];
          for (var i = 0; i < response.data.data.bugsTagsRepartition.length; ++i) {
            $scope.statistics.issue.repartition.tag.chart.label.push(response.data.data.bugsTagsRepartition[i].name);
            $scope.statistics.issue.repartition.tag.chart.data.push(response.data.data.bugsTagsRepartition[i].value);
          }
        }

        // Customer count (values)
        $scope.statistics.customer.actual = response.data.data.customerAccessNumber.actual;
        $scope.statistics.customer.maximum = response.data.data.customerAccessNumber.maximum;

        // Total tasks (value)
        $scope.statistics.task.total = response.data.data.totalTasks;        

        // Current tasks (value)
        $scope.statistics.task.current = response.data.data.taskStatus.doing;

        // Finished tasks (value)
        $scope.statistics.task.done = response.data.data.taskStatus.done;

        // Late tasks (value)
        $scope.statistics.task.late = response.data.data.taskStatus.late;

        // Task repartition (tasks/user ratio) (pie)
        if (!response.data.data.tasksRepartition || !response.data.data.tasksRepartition.length)
          $scope.statistics.task.repartition.valid = false;
        else {
          $scope.statistics.task.repartition.chart = {};
          $scope.statistics.task.repartition.chart.label = [];
          $scope.statistics.task.repartition.chart.data = [];
          for (var i = 0; i < response.data.data.tasksRepartition.length; ++i) {
            $scope.statistics.task.repartition.chart.label.push(response.data.data.tasksRepartition[i].user.firstname + " " + response.data.data.tasksRepartition[i].user.lastname);
            $scope.statistics.task.repartition.chart.data.push(response.data.data.tasksRepartition[i].value);
          }
        }

        // Talk messages count and total (values)
        $scope.statistics.talk.team = response.data.data.timelinesMessageNumber.team;
        $scope.statistics.talk.customer = response.data.data.timelinesMessageNumber.customer;
        $scope.statistics.talk.total = +$scope.statistics.talk.team + +$scope.statistics.talk.customer;

        // Task status repartition per user (user task advancement) (bar) -->
        if (!response.data.data.userTasksAdvancement || !response.data.data.userTasksAdvancement.length)
          $scope.statistics.user.advancement.valid = false;
        else {
          $scope.statistics.user.advancement.chart = {};
          $scope.statistics.user.advancement.chart.label = [];
          $scope.statistics.user.advancement.chart.data = [];
          $scope.statistics.user.advancement.chart.series = ["Doing", "Done", "Late", "To Do"];

          var doing = [];
          var done = [];
          var late = [];
          var toDo = [];
          for (var i = 0; i < response.data.data.userTasksAdvancement.length; ++i) {
            $scope.statistics.user.advancement.chart.label.push(response.data.data.userTasksAdvancement[i].user.firstname + " " + response.data.data.userTasksAdvancement[i].user.lastname);
            doing.push(response.data.data.userTasksAdvancement[i].tasksDoing);
            done.push(response.data.data.userTasksAdvancement[i].tasksDone);
            late.push(response.data.data.userTasksAdvancement[i].tasksLate);
            toDo.push(response.data.data.userTasksAdvancement[i].tasksToDo);
          }
          $scope.statistics.user.advancement.chart.data.push(doing, done, late, toDo);
        }

        // User working charge (bar)
        if (!response.data.data.userWorkingCharge || !response.data.data.userWorkingCharge.length)
          $scope.statistics.user.charge.valid = false;
        else {
          $scope.statistics.user.charge.chart = {};
          $scope.statistics.user.charge.chart.label = [];
          $scope.statistics.user.charge.chart.data = [];
          $scope.statistics.user.charge.chart.series = ["Charge"];

          var charge = [];
          for (var i = 0; i < response.data.data.userWorkingCharge.length; ++i) {
            $scope.statistics.user.charge.chart.label.push(response.data.data.userWorkingCharge[i].user.firstname + " " + response.data.data.userWorkingCharge[i].user.lastname);
            $scope.statistics.user.charge.chart.data.push(response.data.data.userWorkingCharge[i].charge);
          }
        }

        // Project advancement (line) -->
        if (!response.data.data.projectAdvancement || !response.data.data.projectAdvancement.length)
          $scope.statistics.project.advancement.valid = false;
        else {
          $scope.statistics.project.advancement.chart = {};
          $scope.statistics.project.advancement.chart.label = [];
          $scope.statistics.project.advancement.chart.data = [];

          for (var i = 0; i < response.data.data.projectAdvancement.length; ++i) {
            $scope.statistics.project.advancement.chart.label.push(response.data.data.projectAdvancement[i].date);
            $scope.statistics.project.advancement.chart.data.push(response.data.data.projectAdvancement[i].finishedTasks);
          }
        }

        $scope.view.loaded = true;
        $scope.view.valid = true;
      }
      else
        $rootScope.reject(true);
    }
    else
      $rootScope.reject(false);
  };

  var statisticsNotReceived = function(response) {
    $scope.view.loaded = true;
    $scope.view.valid = false;
    if (response && response.data && response.data.info && response.data.info.return_code)
      switch(response.data.info.return_code) {
        case "16.1.3":
        $rootScope.reject(false);
        break;

        case "16.1.4":
        deferred.reject(false);
        $rootScope.project.logout(true);
        break;

        default:
        $rootScope.project.logout(true);
        break;
      }
    else
      $rootScope.reject(true);
  };

  // Get project's statistics
  $http.get($rootScope.api.url + "/statistics/" + $scope.statistics.project_id, { headers: { 'Authorization': $rootScope.user.token }}).then(
    function onSuccess(response) { statisticsReceived(response) },
    function onError(response) { statisticsNotReceived(response) }
  );

}]);