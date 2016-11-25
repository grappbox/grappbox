/*
Summary: Dashboard Controller
*/

angular.module('GrappBox.controllers')

.controller('DashboardCtrl', function ($scope, $rootScope, $state, $stateParams, Toast, Dashboard, Users, Projects, Stats) {

  // Disable back button & set nav color
  $scope.$on('$ionicView.beforeEnter', function (event, viewData) {
    $rootScope.viewColor = $rootScope.GBNavColors.dashboard;
    viewData.enableBack = false;
  });


  $scope.doRefreshTeamOccupation = function () {
    $scope.GetTeamOccupation();
    console.log("View refreshed !");
  }

  $scope.doRefreshNextMeetings = function () {
    $scope.GetNextMeetings();
    console.log("View refreshed !");
  }

  $scope.doRefreshStats = function () {
    $scope.GetStats();
    console.log("View refreshed !");
  }

  /*$scope.doRefreshGlobalProgress = function () {
  $scope.GetGlobalProgress();
  console.log("View refreshed !");
}*/

// Just to know if we are in a project or not for UI
$rootScope.hasProject = true;

console.log("PROJECTID = " + $stateParams.projectId);
$rootScope.projectId = $stateParams.projectId;
$scope.projectId = $stateParams.projectId;

//Get Team Occupation
$scope.teamOccupationTab = {};
$scope.GetTeamOccupation = function () {
  //$rootScope.showLoading();
  Dashboard.TeamOccupation().get({
    id: $scope.projectId
  }).$promise
  .then(function (data) {
    console.log('Get team occupation list successful !');
    console.log(data.data.array);
    $scope.teamOccupationTab = data.data.array;
    if (data.data.array.length == 0)
    $scope.noTeam = "You don't have team.";
    else
    $scope.noTeam = false;
  })
  .catch(function (error) {
    console.error('Get team occupation list failed ! Reason: ' + error.status + ' ' + error.statusText);
    console.error(error);
  })
  .finally(function () {
    $scope.$broadcast('scroll.refreshComplete');
    //$rootScope.hideLoading();
  })
}
$scope.GetTeamOccupation();

//Get Next Meetings
$scope.nextMeetingsTab = {};
$scope.GetNextMeetings = function () {
  //$rootScope.showLoading();
  Dashboard.NextMeetings().get({
    id: $scope.projectId
  }).$promise
  .then(function (data) {
    console.log('Get next meetings list successful !');
    console.log(data.data);
    $scope.nextMeetingsTab = data.data.array;
    if (data.data.array.length < 1)
    $scope.noMeeting = "You don't have meeting.";
    else
    $scope.noMeeting = false;
  })
  .catch(function (error) {
    console.error('Get next meetings list failed ! Reason: ' + error.status + ' ' + error.statusText);
  })
  .finally(function () {
    $scope.$broadcast('scroll.refreshComplete');
    //$rootScope.hideLoading();
  })
}
$scope.GetNextMeetings();

//Get Stats
$scope.GetStats = function () {
  //$rootScope.showLoading();
  Stats.GetStats().get({
    projectId: $scope.projectId
  }).$promise
  .then(function (data) {
    console.log('Get stats list successful !');
    console.log(data.data);
    AssignStats(data.data);
  })
  .catch(function (error) {
    console.error('Get stats list failed ! Reason: ' + error.status + ' ' + error.statusText);
    console.error(error);
  })
  .finally(function () {
    $scope.$broadcast('scroll.refreshComplete');
    //$rootScope.hideLoading();
  })
}
$scope.GetStats();


//Assign stats
var AssignStats = function (stats) {

  $scope.stats = {};
  $scope.stats.options = {legend: {display: true}};

  /*
  ** Errors & stats management
  */
  $scope.errors = {};
  //bugAssignationTracker
  if (stats.bugAssignationTracker.length == 0)
    $scope.errors.bugAssignationTracker = "There is no bug on BugTracker";
  else {
    //$scope.errors.bugAssignationTracker = false;
    $scope.stats.bugAssignationTracker = {};
    $scope.stats.bugAssignationTracker.label = ["Assigned", "Unassigned"];
    $scope.stats.bugAssignationTracker.data = [
      stats.bugAssignationTracker.assigned,
      stats.bugAssignationTracker.unassigned
    ];
  }
  //bugsTagsRepartition
  if (stats.bugsTagsRepartition.length == 0)
    $scope.errors.bugsTagsRepartition = "There is no tag on BugTracker";
  else {
    //$scope.errors.bugsTagsRepartition = false;
    $scope.stats.bugsTagsRepartition = {};
    $scope.stats.bugsTagsRepartition.label = [];
    $scope.stats.bugsTagsRepartition.data = [];
    for(var i = 0; i < stats.bugsTagsRepartition.length; i++) {
      $scope.stats.bugsTagsRepartition.label.push(stats.bugsTagsRepartition[i].name);
      $scope.stats.bugsTagsRepartition.data.push(stats.bugsTagsRepartition[i].percentage);
    }
  }
  //bugsUsersRepartition
  if (stats.bugsUsersRepartition.length == 0)
    $scope.errors.bugsUsersRepartition = "There is no bug on BugTracker";
  else {
    $scope.stats.bugsUsersRepartition = {};
    $scope.stats.bugsUsersRepartition.label = [];
    $scope.stats.bugsUsersRepartition.data = [];
    for(var i = 0; i < stats.bugsUsersRepartition.length; i++) {
      $scope.stats.bugsUsersRepartition.label.push(stats.bugsUsersRepartition[i].user.firstname + " " + stats.bugsUsersRepartition[i].user.lastname);
      $scope.stats.bugsUsersRepartition.data.push(stats.bugsUsersRepartition[i].value);
    }
  }
  //tasksStatus
  if (!stats.taskStatus)
    $scope.errors.taskStatus = "There is no task.";
  else {
    $scope.stats.taskStatus = {};
    $scope.stats.taskStatus.label = [];
    $scope.stats.taskStatus.data = [];
    for (task in stats.taskStatus) {
      $scope.stats.taskStatus.label.push(task);
      $scope.stats.taskStatus.data.push(stats.taskStatus[task]);
    }
  }
  //tasksRepartition
  if (stats.tasksRepartition.length == 0)
    $scope.errors.tasksRepartition = "There is no task assigned yet.";
  else {
    $scope.stats.tasksRepartition = {};
    $scope.stats.tasksRepartition.label = [];
    $scope.stats.tasksRepartition.data = [];
    for(var i = 0; i < stats.tasksRepartition.length; i++) {
      $scope.stats.tasksRepartition.label.push(stats.tasksRepartition[i].user.firstname + " " + stats.tasksRepartition[i].user.lastname);
      $scope.stats.tasksRepartition.data.push(stats.tasksRepartition[i].value);
    }
  }
  //timelinesMessageNumber
  if (!stats.timelinesMessageNumber)
    $scope.errors.timelinesMessageNumber = "There is no timeline message.";
  else {
    $scope.stats.timelinesMessageNumber = {};
    $scope.stats.timelinesMessageNumber.label = [];
    $scope.stats.timelinesMessageNumber.data = [];
    for (mess in stats.timelinesMessageNumber) {
      $scope.stats.timelinesMessageNumber.label.push(mess);
      $scope.stats.timelinesMessageNumber.data.push(stats.timelinesMessageNumber[mess]);
    }
  }
  //userTasksAdvancement
  if (stats.userTasksAdvancement.length == 0)
    $scope.errors.userTasksAdvancement = "There is no task assigned yet.";
  else {
    $scope.stats.userTasksAdvancement = {};
    $scope.stats.userTasksAdvancement.label = [];
    $scope.stats.userTasksAdvancement.data = [];
    $scope.stats.userTasksAdvancement.series = ["Doing", "Done", "Late", "To Do"];
    var doing = [];
    var done = [];
    var late = [];
    var toDo = [];
    for(var i = 0; i < stats.userTasksAdvancement.length; i++) {
      $scope.stats.userTasksAdvancement.label.push(stats.userTasksAdvancement[i].user.firstname + " " + stats.userTasksAdvancement[i].user.lastname);
      doing.push(stats.userTasksAdvancement[i].tasksDoing);
      done.push(stats.userTasksAdvancement[i].tasksDone);
      late.push(stats.userTasksAdvancement[i].tasksLate);
      toDo.push(stats.userTasksAdvancement[i].tasksLate);
    }
    $scope.stats.userTasksAdvancement.data.push(doing, done, late, toDo);
  }
  //userWorkingCharge
  if (stats.userWorkingCharge.length == 0)
    $scope.errors.userWorkingCharge = "There is no task assigned yet.";
  else {
    $scope.stats.userWorkingCharge = {};
    $scope.stats.userWorkingCharge.label = [];
    $scope.stats.userWorkingCharge.data = [];
    $scope.stats.userWorkingCharge.series = ["Charge"];
    var charge = [];
    for(var i = 0; i < stats.userWorkingCharge.length; i++) {
      $scope.stats.userWorkingCharge.label.push(stats.userWorkingCharge[i].user.firstname + " " + stats.userWorkingCharge[i].user.lastname);
      charge.push(stats.userWorkingCharge[i].charge);
    }
    $scope.stats.userWorkingCharge.data.push(charge);
  }
  //clientBugTracker
  if (!stats.clientBugTracker)
    $scope.errors.clientBugTracker = "There is no bug from clients yet.";
  else {
    $scope.stats.clientBugTracker = stats.clientBugTracker;
  }
  //customerAccessNumber
  if (!stats.customerAccessNumber)
    $scope.errors.customerAccessNumber = "There is no client access yet.";
  else {
    $scope.stats.customerAccessNumber = stats.customerAccessNumber.actual;
  }
  //totalTasks
  if (!stats.totalTasks)
    $scope.errors.totalTasks = "There is no task yet.";
  else {
    $scope.stats.totalTasks = stats.totalTasks;
  }
  //storageSize
  if (!stats.storageSize)
    $scope.errors.storageSize = "There is no task yet.";
  else {
    $scope.stats.totalStorageSize = stats.storageSize.total;
    $scope.stats.occupiedStorageSize = stats.storageSize.occupied;
  }
  //openCloseBug
  if (!stats.openCloseBug)
    $scope.errors.openCloseBug = "There is no bug yet.";
  else {
    $scope.stats.openBug = stats.openCloseBug.open;
    $scope.stats.closeBug = stats.openCloseBug.closed;
  }
  //projectTimeLimits
  if (!stats.projectTimeLimits)
    $scope.errors.projectTimeLimits = "There is no limit set yet.";
  else {
    $scope.stats.projectStart = stats.projectTimeLimits.projectStart.date;
    $scope.stats.projectEnd = stats.projectTimeLimits.projectEnd.date;
  }
  //lateTask
  if (stats.lateTask.length == 0)
    $scope.errors.lateTask = "There is no task assigned yet.";
  else {
    // $scope.lateTask = stats.lateTask;
    $scope.stats.lateTask = {};
    $scope.stats.lateTask.label = [];
    $scope.stats.lateTask.data = [];
    $scope.stats.lateTask.series = ["On time", "Late"];
    var onTime = [];
    var late = [];
    for(var i = 0; i < stats.lateTask.length; i++) {
      $scope.stats.lateTask.label.push(stats.lateTask[i].user.firstname + " " + stats.lateTask[i].user.lastname);
      onTime.push(stats.lateTask[i].ontimeTasks);
      late.push(stats.lateTask[i].lateTasks);
    }
    $scope.stats.lateTask.data.push(onTime, late);
  }
}

// TEST
// $scope.labels = ["January", "February", "March", "April", "May", "June", "July"];
// $scope.series = ['Series A', 'Series B'];
// $scope.data = [
//   [65, 59, 80, 81, 56, 55, 40],
//   [28, 48, 40, 19, 86, 27, 90]
// ];
// $scope.onClick = function (points, evt) {
//   console.log(points, evt);
// };
// $scope.datasetOverride = [{ yAxisID: 'y-axis-1' }, { yAxisID: 'y-axis-2' }];
// $scope.options = {
//   scales: {
//     yAxes: [
//       {
//         id: 'y-axis-1',
//         type: 'linear',
//         display: true,
//         position: 'left'
//       },
//       {
//         id: 'y-axis-2',
//         type: 'linear',
//         display: true,
//         position: 'right'
//       }
//     ]
//   }
// };

/*
** Get users avatars
** Method: GET
*/
$scope.usersAvatars = {};
$scope.GetUsersAvatars = function () {
  //$rootScope.showLoading();
  Users.Avatars().get({
    projectId: $scope.projectId
  }).$promise
  .then(function (data) {
    console.log('Get members avatars successful !');
    $scope.usersAvatars = data.data.array;
    console.log(data);
  })
  .catch(function (error) {
    console.error('Get members avatars failed ! Reason: ' + error.status + ' ' + error.statusText);
    console.error(error);
  })
  .finally(function () {
    $scope.$broadcast('scroll.refreshComplete');
    //$rootScope.hideLoading();
  })
}
$scope.GetUsersAvatars();

// Find user avatar
$scope.findUserAvatar = function (id) {
  for (var i = 0; i < $scope.usersAvatars.length; i++) {
    if ($scope.usersAvatars[i].userId === id) {
      return $scope.usersAvatars[i].avatar;
    }
  }
}

/*
** Get project logo
** Method: GET
*/
$scope.projectLogo = {};
$scope.GetProjectLogo = function () {
  //$rootScope.showLoading();
  Projects.Logo().get({
    id: $scope.projectId
  }).$promise
  .then(function (data) {
    console.log('Get project logo successful !');
    $scope.projectLogo.logo = data.data.logo;
  })
  .catch(function (error) {
    console.error('Get project logo failed ! Reason: ' + error.status + ' ' + error.statusText);
  })
  .finally(function () {
    $scope.$broadcast('scroll.refreshComplete');
    //$rootScope.hideLoading();
  })
}
$scope.GetProjectLogo();

//Get Global Progress
/*$scope.globalProgressTab = {};
$scope.GetGlobalProgress = function () {
  $rootScope.showLoading();
  Dashboard.GlobalProgress().get({
    token: $rootScope.userDatas.token
  }).$promise
  .then(function (data) {
    console.log('Get global progress list successful !');
    console.log(data.data);
    $scope.globalProgressTab = data.data.array;
    if (data.length == 0)
    $scope.noProject = "You don't have project now.";
  })
  .catch(function (error) {
    console.error('Get global progress list failed ! Reason: ' + error.status + ' ' + error.statusText);
  })
  .finally(function () {
    $scope.$broadcast('scroll.refreshComplete');
    $rootScope.hideLoading();
  })
}
$scope.GetGlobalProgress();*/

})
