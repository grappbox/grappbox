/*
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

// Controller definition
// APP profile settings
app.controller("StatisticsController", ["$http", "notificationFactory", "$rootScope", "$route", "$scope", "statisticFactory",
    function($http, notificationFactory, $rootScope, $route, $scope, statisticFactory) {

  /* ==================== INITIALIZATION ==================== */

  // Scope variables initialization
  $scope.view = { loaded: false, valid: false };
  $scope.statistics = {
    project_id: $route.current.params.project_id,
    issue: {
      assignation: { assigned: "", unassigned: "" },
      byCustomer: "",
      evolution: "",
      repartition: {
        tag: { chart: "", empty: true, message : "" },
        user: { chart: "", empty: true, message : "" }
      }
    },
    customer: { actual: "", maximum: "" },
    project: { advancement: "", dates: "" },
    storage: { status: "" },
    task: {
      current: "",
      done: "",
      late: "",
      total: "",
      repartition: { chart: "", empty: true, message : "" }
    },
    talk: { count: { team: "", customer: "" } },
    user: { advancement: "", charge: "" }
  };
  $scope.error = {
    issue: {
      none: "Your project doesn't have any issue.",
      repartition: { tag: "None of your tasks are assigned to a tag.", user: "None of your issues are assigned to a user." }
    },
    tag: "Your project's bugtracker doesn't have any tag.",
    task: {
      none: "Your project doesn't have any task.",
      repartition: "None of your tasks are assigned to a user."
    },
    user: "Your project doesn't have any user."
  };



  /* ==================== GET PROJECT STATISTICS ==================== */

  var statisticsReceived = function(response) {
    if (response && response.data && response.data.info && response.data.info.return_code) {
      if (response.data.info.return_code == "1.16.1") {

        // Opened/closed issues (values)
        $scope.statistics.issue.assignation.assigned = response.data.data.bugAssignationTracker.assigned;
        $scope.statistics.issue.assignation.unassigned = response.data.data.bugAssignationTracker.unassigned;

        // Issues/user ratio (pie)
        $scope.statistics.issue.repartition.user.chart = statisticFactory.pie(1, "##");
        $scope.statistics.issue.repartition.user.chart.data.push(["User", "Issues"]);
        if (!response.data.data.bugsUsersRepartition.length)
          $scope.statistics.issue.repartition.user.message = (response.data.data.bugAssignationTracker.assigned + response.data.data.bugAssignationTracker.unassigned ? $scope.error.user : $scope.error.issue.none);
        else {
          angular.forEach(response.data.data.bugsUsersRepartition, function(key, value) {
            this.statistics.issue.repartition.user.chart.data.push([key.user, key.value]);
            if (key.value)
              this.statistics.issue.repartition.user.empty = false;
          }, $scope);
          if ($scope.statistics.issue.repartition.user.empty)
            $scope.statistics.issue.repartition.user.message = $scope.error.issue.repartition.user;
        }

        // Customer issues (value)
        $scope.statistics.issue.byCustomer = response.data.data.clientBugTracker;

        // Issues/tags ratio (pie)
        $scope.statistics.issue.repartition.tag.chart = statisticFactory.pie(1, "##");
        $scope.statistics.issue.repartition.tag.chart.data.push(["Tags", "Issues"]);
        if (!response.data.data.bugsTagsRepartition.length)
          $scope.statistics.issue.repartition.tag.message = (response.data.data.bugAssignationTracker.assigned + response.data.data.bugAssignationTracker.unassigned ? $scope.error.tag : $scope.error.issue.none);
        else {
          angular.forEach(response.data.data.bugsTagsRepartition, function(key, value) {
            this.statistics.issue.repartition.tag.chart.data.push([key.user, key.value]);
            if (key.value)
              this.statistics.issue.repartition.tag.empty = false;
          }, $scope);
          if ($scope.statistics.issue.repartition.tag.empty)
            $scope.statistics.issue.repartition.tag.message = $scope.error.issue.repartition.tag;
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

        // Tasks/user ratio (pie)
        $scope.statistics.task.repartition.chart = statisticFactory.pie(1, "##");
        $scope.statistics.task.repartition.chart.data.push(["User", "Tasks"]);
        if (!response.data.data.tasksRepartition.length)
          $scope.statistics.task.repartition.message = (response.data.data.totalTasks ? $scope.error.user : $scope.error.task.none);
        else {
          angular.forEach(response.data.data.tasksRepartition, function(key, value) {
            this.statistics.task.repartition.chart.data.push([key.user, key.value]);
            if (key.value)
              this.statistics.task.repartition.empty = false;
          }, $scope);
          if ($scope.statistics.task.repartition.empty)
            $scope.statistics.task.repartition.message = $scope.error.task.repartition;
        }

        // Timelines messages (values)
        $scope.statistics.talk.count.team = response.data.data.timelinesMessageNumber.team;
        $scope.statistics.talk.count.customer = response.data.data.timelinesMessageNumber.customer;

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