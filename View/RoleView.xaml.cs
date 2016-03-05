using GrappBox.Model;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class RoleView : Page
    {
        ProjectSettingsViewModel vm = ProjectSettingsViewModel.GetViewModel();

        static private RoleView instance = null;
        static public RoleView GetInstance()
        {
            return instance;
        }
        public RoleView()
        {
            this.InitializeComponent();
            this.DataContext = vm;
            instance = this;
        }

        /// <summary>
        /// Invoked when this page is about to be displayed in a Frame.
        /// </summary>
        /// <param name="e">Event data that describes how this page was reached.
        /// This parameter is typically used to configure the page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            if (e.Parameter != null)
            {
                Btn.Content = "Update";
                Btn.Click += Update_Click;
                Btn.Click -= AddRole_Click;
                vm.getRole();
                //TeamTimeline
                if (vm.TeamTimeline == 0)
                    TTNButton.IsChecked = true;
                else if (vm.TeamTimeline == 1)
                    TTRradioButton.IsChecked = true;
                else
                    TTRWradioButton.IsChecked = true;

                //CustomerTimeline
                if (vm.CustomerTimeline == 0)
                    CTNButton.IsChecked = true;
                else if (vm.CustomerTimeline == 1)
                    CTRradioButton.IsChecked = true;
                else
                    CTRWradioButton.IsChecked = true;

                //Gantt
                if (vm.Gantt == 0)
                    GNButton.IsChecked = true;
                else if (vm.Gantt == 1)
                    GRradioButton.IsChecked = true;
                else
                    GRWradioButton.IsChecked = true;

                //Whiteboard
                if (vm.Whiteboard == 0)
                    WNButton.IsChecked = true;
                else if (vm.Whiteboard == 1)
                    WRradioButton.IsChecked = true;
                else
                    WRWradioButton.IsChecked = true;

                //Bugtracker
                if (vm.Bugtracker == 0)
                    BNButton.IsChecked = true;
                else if (vm.Bugtracker == 1)
                    BRradioButton.IsChecked = true;
                else
                    BRWradioButton.IsChecked = true;

                //Event
                if (vm.Event == 0)
                    ENButton.IsChecked = true;
                else if (vm.Event == 1)
                    ERradioButton.IsChecked = true;
                else
                    ERWradioButton.IsChecked = true;

                //Task
                if (vm.Task == 0)
                    TNButton.IsChecked = true;
                else if (vm.Task == 1)
                    TRradioButton.IsChecked = true;
                else
                    TRWradioButton.IsChecked = true;

                //ProjectSettings
                if (vm.ProjectSettings == 0)
                    PSNButton.IsChecked = true;
                else if (vm.ProjectSettings == 1)
                    PSRradioButton.IsChecked = true;
                else
                    PSRWradioButton.IsChecked = true;

                //Cloud
                if (vm.Cloud == 0)
                    CNButton.IsChecked = true;
                else if (vm.Cloud == 1)
                    CRradioButton.IsChecked = true;
                else
                    CRWradioButton.IsChecked = true;
            }
            else
            {
                Btn.Content = "Add";
                Btn.Click += AddRole_Click;
                Btn.Click -= Update_Click;
            }
        }

        #region menuClicked
        private void WhiteboardButton_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(WhiteBoardView));
        }

        private void UserSettingsButton_Click(object sender, RoutedEventArgs e)
        {
            UserSettingsViewModel usvm = new UserSettingsViewModel();
            usvm.getAPI();
            this.Frame.Navigate(typeof(UserView));
        }

        private void DashboardButton_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(DashBoardView));
        }

        private void ProjectSettingsButton_Click(object sender, RoutedEventArgs e)
        {
            ProjectSettingsViewModel psvm = new ProjectSettingsViewModel();
            psvm.getProjectSettings();
            psvm.getProjectUsers();
            psvm.getCustomerAccesses();
            psvm.getRoles();
            this.Frame.Navigate(typeof(ProjectSettingsView));
        }
        #endregion menuClicked

        private void Button_Checked(object sender, RoutedEventArgs e)
        {
            RadioButton btn = sender as RadioButton;
            int res;
            if (btn.Content.ToString() == "None")
                res = 0;
            else if (btn.Content.ToString() == "Read")
                res = 1;
            else
                res = 2;

            switch (btn.GroupName)
            {
                case "TeamTimeline":
                    vm.TeamTimeline = res;
                    break;
                case "CustomerTimeline":
                    vm.CustomerTimeline = res;
                    break;
                case "Gantt":
                    vm.Gantt = res;
                    break;
                case "Whiteboard":
                    vm.Whiteboard = res;
                    break;
                case "Bugtracker":
                    vm.Bugtracker = res;
                    break;
                case "Event":
                    vm.Event = res;
                    break;
                case "Task":
                    vm.Task = res;
                    break;
                case "ProjectSettings":
                    vm.ProjectSettings = res;
                    break;
                case "Cloud":
                    vm.Cloud = res;
                    break;
                default:
                    break;
            }
        }

        private void Remove_Click(object sender, RoutedEventArgs e)
        {
            vm.removeUserRole();
        }

        private void Add_Click(object sender, RoutedEventArgs e)
        {
            vm.assignUserRole();
        }

        private void userAssign_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.UserAssignedSelected = (sender as ListBox).SelectedItem as ProjectUserModel;
        }

        private void userNonAssign_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.UserNonAssignedSelected = (sender as ListBox).SelectedItem as ProjectUserModel;
        }

        private void Update_Click(object sender, RoutedEventArgs e)
        {
            if (Name.Text != "")
            {
                Name.BorderBrush = new SolidColorBrush();
                setValues();
                vm.updateRole();
            }
            else
                Name.BorderBrush = new SolidColorBrush(Colors.Red);
        }

        private void AddRole_Click(object sender, RoutedEventArgs e)
        {
            if (Name.Text != "")
            {
                Name.BorderBrush = new SolidColorBrush();
                setValues();
                vm.addRole();
            }
            else
                Name.BorderBrush = new SolidColorBrush(Colors.Red);
        }

        private void setValues()
        {
            vm.RoleName = Name.Text;

            //TeamTimeline
            if (TTNButton.IsChecked == true)
                vm.TeamTimeline = 0;
            else if (TTRradioButton.IsChecked == true)
                vm.TeamTimeline = 1;
            else
                vm.TeamTimeline = 2;

            //CustomerTimeline
            if (CTNButton.IsChecked == true)
                vm.CustomerTimeline = 0;
            else if (CTRradioButton.IsChecked == true)
                vm.CustomerTimeline = 1;
            else
                vm.CustomerTimeline = 2;

            //Gantt
            if (GNButton.IsChecked == true)
                vm.Gantt = 0;
            else if (GRradioButton.IsChecked == true)
                vm.Gantt = 1;
            else
                vm.Gantt = 2;

            //Whiteboard
            if (WNButton.IsChecked == true)
                vm.Whiteboard = 0;
            else if (WRradioButton.IsChecked == true)
                vm.Whiteboard = 1;
            else
                vm.Whiteboard = 2;

            //Bugtracker
            if (BNButton.IsChecked == true)
                vm.Bugtracker = 0;
            else if (BRradioButton.IsChecked == true)
                vm.Bugtracker = 1;
            else
                vm.Bugtracker = 2;

            //Event
            if (ENButton.IsChecked == true)
                vm.Event = 0;
            else if (ERradioButton.IsChecked == true)
                vm.Event = 1;
            else
                vm.Event = 2;

            //Task
            if (TNButton.IsChecked == true)
                vm.Task = 0;
            else if (TRradioButton.IsChecked == true)
                vm.Task = 1;
            else
                vm.Task = 2;

            //ProjectSettings
            if (PSNButton.IsChecked == true)
                vm.ProjectSettings = 0;
            else if (PSRradioButton.IsChecked == true)
                vm.ProjectSettings = 1;
            else
                vm.ProjectSettings = 2;

            //Cloud
            if (CNButton.IsChecked == true)
                vm.Cloud = 0;
            else if (CRradioButton.IsChecked == true)
                vm.Cloud = 1;
            else
                vm.Cloud = 2;
        }
    }
}
