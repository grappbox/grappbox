using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.Model;
using Grappbox.ViewModel;
using System.Threading.Tasks;
using Windows.UI.Popups;
using System.Collections.ObjectModel;
using Grappbox.Helpers;
using Windows.Web.Http;
using Grappbox.CustomControls;
using System.Diagnostics;


namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class TaskDetailView : Page
    {
        private string ErrorMessage = "";
        private bool IsEditing;
        public TaskDetailView()
        {
            this.InitializeComponent();
        }

        protected override async void OnNavigatedTo(NavigationEventArgs e)
        {
            base.OnNavigatedTo(e);
            LoaderDialog loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            loader.ShowAsync();
            await viewModel.getTagList();
            await viewModel.GetUsersList();
            await viewModel.GetTasks();
            var session = SessionHelper.GetSession();
            if (e.Parameter != null)
            {
                IsEditing = true;
                viewModel.Model = e.Parameter as TaskModel;
                viewModel.AssignedUsers = new ObservableCollection<TaskUserModel>(viewModel.Model.Users);
                viewModel.AssignedTaskList = new ObservableCollection<TaskModel>(viewModel.Model.Tasks);
                viewModel.DependenciesList = new ObservableCollection<DependencyTask>(viewModel.Model.Dependencies);
                viewModel.AssignedTagList = new ObservableCollection<TagModel>(viewModel.Model.Tags);
                if (viewModel.Model.DueDate != null)
                    DueDatePicker.Date = new DateTimeOffset((DateTime)viewModel.Model.DueDate);
                if (viewModel.Model.StartedAt != null)
                    StartDatePicker.Date = new DateTimeOffset((DateTime)viewModel.Model.StartedAt);
            }
            else
            {
                IsEditing = false;
                viewModel.Model = new TaskModel()
                {
                    Title = "",
                    Description = "",
                    Advance = 0,
                    CreatedAt = null,
                    Creator = new Creator()
                    {
                        Id = session.UserId,
                        FirstName = session.UserViewModel.Firstname,
                        LastName = session.UserViewModel.Lastname,
                    },
                    Dependencies = null,
                    Tasks = null,
                    DueDate = null,
                    FinishedAt = null,
                    Id = 0,
                    IsContainer = false,
                    IsMilestone = false,
                    ProjectId = session.ProjectId,
                    StartedAt = null,
                    Tags = null,
                    TasksModified = null,
                    Users = null
                };
            }
            if (viewModel.Model.IsContainer)
            {
                IsContainerCheckBox.IsChecked = true;
                IsContainerCheckBox_Checked(null, null);
            }
            else if (viewModel.Model.IsMilestone)
            {
                IsMilestoneCheckBox.IsChecked = true;
                IsMilestoneCheckBox_Checked(null, null);
            }
            loader.Hide();
        }

        private async Task<bool> CheckData()
        {
            bool result = true;
            if (string.IsNullOrWhiteSpace(this.Title.Text))
            {
                ErrorMessage = "Title can't be empty";
                result = false;
            }
            else if (string.IsNullOrWhiteSpace(this.DescriptionText.Text))
            {
                ErrorMessage = "Description can't be empty";
                result = false;
            }
            MessageDialog dialog = new MessageDialog("");
            if (result == false)
                await dialog.ShowAsync();
            return result;
        }

        private async void Save(object sender, RoutedEventArgs e)
        {
            viewModel.Model.DueDate = DueDatePicker.Date?.Date;
            viewModel.Model.StartedAt = StartDatePicker.Date?.Date;
            if (await CheckData() == false)
                return;
            bool res = false;
            LoaderDialog loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            loader.ShowAsync();
            if (IsEditing)
                res = await viewModel.EditTask();
            else
                res = await viewModel.CreateTask();
            loader.Hide();
            if (res == true)
            {
                MessageDialog dialog = new MessageDialog("Success");
                await dialog.ShowAsync();
            }
            else
            {
                MessageDialog dialog = new MessageDialog("Error - can't create task");
                await dialog.ShowAsync();
            }
            if (this.Frame.CanGoBack == true)
                this.Frame.GoBack();
        }

        private void Cancel(object sender, RoutedEventArgs e)
        {
            if (this.Frame.CanGoBack == true)
                this.Frame.GoBack();
        }

        private void DeleteParticipant(object sender, RoutedEventArgs e)
        {
            var button = sender as Button;
            var user = button.DataContext as TaskUserModel;
            viewModel.AssignedUsers.Remove(user);
        }
        private void DeleteTask(object sender, RoutedEventArgs e)
        {
            var button = sender as Button;
            var task = button.DataContext as TaskModel;
            viewModel.AssignedTaskList.Remove(task);
        }
        private void DeleteDependencie(object sender, RoutedEventArgs e)
        {
            var button = sender as Button;
            var dep = button.DataContext as DependencyTask;
            viewModel.DependenciesList.Remove(dep);
        }
        private void DeleteTag(object sender, RoutedEventArgs e)
        {
            var button = sender as Button;
            var tag = button.DataContext as TagModel;
            viewModel.AssignedTagList.Remove(tag);
        }

        private void IsMilestoneCheckBox_Checked(object sender, RoutedEventArgs e)
        {
            this.IsContainerCheckBox.IsChecked = false;
            viewModel.Model.IsContainer = false;
            viewModel.Model.IsMilestone = true;
            this.tasksList.Visibility = Visibility.Collapsed;
            this.assignedUsersList.Visibility = Visibility.Collapsed;
            this.dependenciesList.Visibility = Visibility.Visible;
            this.TagGridView.Visibility = Visibility.Collapsed;
            this.StartDatePicker.Visibility = Visibility.Collapsed;
            this.DueDatePicker.Visibility = Visibility.Visible;
        }

        private void IsMilestoneCheckBox_Unchecked(object sender, RoutedEventArgs e)
        {
            viewModel.Model.IsMilestone = false;
            this.assignedUsersList.Visibility = Visibility.Visible;
            this.dependenciesList.Visibility = Visibility.Visible;
            this.TagGridView.Visibility = Visibility.Visible;
            this.StartDatePicker.Visibility = Visibility.Visible;
        }

        private void IsContainerCheckBox_Checked(object sender, RoutedEventArgs e)
        {
            this.IsMilestoneCheckBox.IsChecked = false;
            viewModel.Model.IsMilestone = false;
            viewModel.Model.IsContainer = true;
            this.tasksList.Visibility = Visibility.Visible;
            this.assignedUsersList.Visibility = Visibility.Collapsed;
            this.dependenciesList.Visibility = Visibility.Collapsed;
            this.TagGridView.Visibility = Visibility.Collapsed;
            this.StartDatePicker.Visibility = Visibility.Collapsed;
            this.DueDatePicker.Visibility = Visibility.Collapsed;
        }
        private void IsContainerCheckBox_Unchecked(object sender, RoutedEventArgs e)
        {
            viewModel.Model.IsContainer = false;
            this.tasksList.Visibility = Visibility.Collapsed;
            this.assignedUsersList.Visibility = Visibility.Visible;
            this.dependenciesList.Visibility = Visibility.Visible;
            this.TagGridView.Visibility = Visibility.Visible;
            this.StartDatePicker.Visibility = Visibility.Visible;
            this.DueDatePicker.Visibility = Visibility.Visible;
        }

        private async void AddDependencyButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new AddDependencyDialog(viewModel.FreeDependencies);
            await dialog.ShowAsync();
            if (dialog.NewTask != null)
                viewModel.DependenciesList.Add(dialog.NewTask);
        }

        private async void AddTaskButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new AddTaskDialog(viewModel.FreeTasks);
            await dialog.ShowAsync();
            if (dialog.NewTask != null)
                viewModel.AssignedTaskList.Add(dialog.NewTask);
        }

        private async void AddResourceButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new AddUserToTaskDialog(viewModel.FreeUsers);
            await dialog.ShowAsync();
            if (dialog.NewResource != null)
                viewModel.AssignedUsers.Add(dialog.NewResource);
        }

        private async void CreateTagButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new CreateTagDialog();
            await dialog.ShowAsync();
            if (dialog.postTagModel == null)
                return;
            LoaderDialog loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            loader.ShowAsync();
            bool res = await this.viewModel.addTag(dialog.postTagModel);
            loader.Hide();
            if (res)
            {
                var successDialog = new MessageDialog("Success");
                await successDialog.ShowAsync();
                viewModel.AssignedTagList.Add(dialog.postTagModel);
            }
            else
            {
                var successDialog = new MessageDialog("Error can't add tag");
                await successDialog.ShowAsync();
            }
        }

        private async void AddTagButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new SelectTagDialog(viewModel.TagList);
            await dialog.ShowAsync();
            if (dialog.SelectedTag != null)
                viewModel.AssignedTagList.Add(dialog.SelectedTag);
        }
    }
}
