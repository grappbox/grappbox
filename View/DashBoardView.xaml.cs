using GrappBox.Model;
using GrappBox.Ressources;
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
using System.Threading.Tasks;
using System.Collections.ObjectModel;
using GrappBox.CustomControler;

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class DashBoardView : Page
    {
        PivotItem team;
        PivotItem issues;
        PivotItem tasks;
        PivotItem meetings;
        DashBoardViewModel dvm;
        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = DashBoardViewModel.GetViewModel();
            team_cb.IsChecked = SettingsManager.getOption<bool>("team_cb");
            meetings_cb.IsChecked = SettingsManager.getOption<bool>("meetings_cb");
            issues_cb.IsChecked = SettingsManager.getOption<bool>("issues_cb");
            tasks_cb.IsChecked = SettingsManager.getOption<bool>("tasks_cb");
        }

        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d’événement décrivant la manière dont l’utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected override async void OnNavigatedTo(NavigationEventArgs e)
        {
            this.dvm = DashBoardViewModel.GetViewModel();
            await ViewModel.DashBoardViewModel.InitialiseAsync(dvm);
            if (SettingsManager.getOption<int>("currentProjectId") != 0)
            {
                this.dvm.ProjectList = new ObservableCollection<ProjectListModel>(this.dvm.ProjectList);
                this.project_Combo.ItemsSource = this.dvm.ProjectList;
                this.project_Combo.SelectedValuePath = "Id";
                this.project_Combo.DisplayMemberPath = "Name";
                this.project_Combo.SelectedValue = SettingsManager.getOption<int>("currentProjectId");
                team = CreateOccupationTab();
                issues = CreateIssuesTab();
                tasks = CreateTasksTab();
                meetings = CreateMeetingsTab();
            }
            else
            {
                await this.dvm.getProjectList();
                this.project_Combo.ItemsSource = this.dvm.ProjectList;
                this.project_Combo.SelectedValuePath = "Id";
                this.project_Combo.DisplayMemberPath = "Name";
                this.project_Combo.SelectedValue = SettingsManager.getOption<int>("currentProjectId");
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

        private void team_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("team_cb", team_cb.IsChecked);
            if (team_cb.IsChecked == true)
                db_pivot.Items.Add(team);
            else
                db_pivot.Items.Remove(team);
        }

        private void meetings_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("meetings_cb", meetings_cb.IsChecked);
            if (meetings_cb.IsChecked == true)
                db_pivot.Items.Add(meetings);
            else
                db_pivot.Items.Remove(meetings);
        }

        private void issues_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("issues_cb", issues_cb.IsChecked);
            if (issues_cb.IsChecked == true)
                db_pivot.Items.Add(issues);
            else
                db_pivot.Items.Remove(issues);
        }

        private void tasks_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("tasks_cb", tasks_cb.IsChecked);
            if (tasks_cb.IsChecked == true)
                db_pivot.Items.Add(tasks);
            else
                db_pivot.Items.Remove(tasks);
        }

        private async void project_Combo_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int p = (int)project_Combo.SelectedValue;
            SettingsManager.setOption("currentProjectId", p);
            SettingsManager.setOption("currentProjectName", this.dvm.ProjectList.First(item => item.Id == p).Name);
            await DashBoardViewModel.InitialiseAsync(this.dvm);
            Debug.WriteLine(SettingsManager.getOption<int>("currentProjectId"));
            Debug.WriteLine(SettingsManager.getOption<string>("currentProjectName"));
        }

        private void initPivotItem(string header, out PivotItem pivotItem)
        {
            pivotItem = new PivotItem();
            pivotItem.Header = header;
            pivotItem.Background = new SolidColorBrush(Colors.White);
            pivotItem.Margin = new Thickness(0, 0, 0, 0);
        }

        public PivotItem CreateOccupationTab()
        {
            PivotItem pivotItem;
            initPivotItem("Occupation", out pivotItem);
            TeamDashBoard td = new TeamDashBoard();
            td.HorizontalAlignment = HorizontalAlignment.Center;
            pivotItem.Content = td;
            this.dvm.NotifyPropertyChanged("OccupationList");
            return pivotItem;
        }

        public PivotItem CreateIssuesTab()
        {
            PivotItem pivotItem;
            initPivotItem("Issues", out pivotItem);
            return pivotItem;
        }

        public PivotItem CreateMeetingsTab()
        {
            PivotItem pivotItem;
            initPivotItem("Meetings", out pivotItem);
            return pivotItem;
        }

        public PivotItem CreateTasksTab()
        {
            PivotItem pivotItem;
            initPivotItem("Tasks", out pivotItem);
            return pivotItem;
        }
    }
}