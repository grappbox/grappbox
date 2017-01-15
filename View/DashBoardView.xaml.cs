using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Foundation.Metadata;
using Windows.Graphics.Display;
using Windows.UI;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using System;
using Grappbox.CustomControls.Stats;

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class DashBoardView : Page
    {
        private PivotItem team;
        private PivotItem meetings;
        private PivotItem projectStats;
        private PivotItem bugtrackerStats;
        private PivotItem tasksStats;
        private PivotItem talksStats;
        private PivotItem customerAccessStats;
        private DashBoardViewModel dvm = DashBoardViewModel.GetViewModel();
        private StatsViewModel svm = StatsViewModel.GetViewModel();

        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = dvm;
            team = new PivotItem();
            meetings = new PivotItem();
        }

        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d’événement décrivant la manière dont l’utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected override async void OnNavigatedTo(NavigationEventArgs e)
        {
            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                DisplayInformation.AutoRotationPreferences = DisplayOrientations.Portrait;
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["RedGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            var loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            loader.ShowAsync();
            await this.dvm.InitialiseAsync();
            await svm.getAllStats();
            team = CreateOccupationTab();
            meetings = CreateMeetingsTab();
            projectStats = CreateProjectTab();
            bugtrackerStats = CreateBugtrackerTab();
            tasksStats = CreateTasksTab();
            talksStats = CreateTalksTab();
            customerAccessStats = CreateCustomerAccessTab();
            InitializePivot();
            loader.Hide();
        }

        private void InitializePivot()
        {
            DbPivot?.Items?.Clear();
            foreach (var m in DashBoardViewModel.ModularList)
            {
                if (m.DisplayName == "Occupation" && m.Selected == true && DbPivot?.Items.Contains(this.team) == false)
                    DbPivot?.Items?.Add(this.team);
                if (m.DisplayName == "Meeting" && m.Selected == true && DbPivot?.Items.Contains(this.meetings) == false)
                    DbPivot?.Items?.Add(this.meetings);
                if (m.DisplayName == "Project Stats" && m.Selected == true && DbPivot?.Items.Contains(this.projectStats) == false)
                    DbPivot?.Items?.Add(this.projectStats);
                if (m.DisplayName == "Bugtracker Stats" && m.Selected == true && DbPivot?.Items.Contains(this.bugtrackerStats) == false)
                    DbPivot?.Items?.Add(this.bugtrackerStats);
                if (m.DisplayName == "Tasks Stats" && m.Selected == true && DbPivot?.Items.Contains(this.tasksStats) == false)
                    DbPivot?.Items?.Add(this.tasksStats);
                if (m.DisplayName == "Talks Stats" && m.Selected == true && DbPivot?.Items.Contains(this.talksStats) == false)
                    DbPivot?.Items?.Add(this.talksStats);
                if (m.DisplayName == "Customer Access Stats" && m.Selected == true && DbPivot?.Items.Contains(this.customerAccessStats) == false)
                    DbPivot?.Items?.Add(this.customerAccessStats);
            }
        }

        private void initPivotItem(string label, string glyph, out PivotItem pivotItem)
        {
            pivotItem = new PivotItem();
            pivotItem.Header = new TabHeader(label, glyph);
        }

        public PivotItem CreateProjectTab()
        {
            PivotItem pivotItem;
            initPivotItem("Project Stats", "\uE8F1", out pivotItem);
            pivotItem.DataContext = svm;
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            ProjectStats td = new ProjectStats();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            return pivotItem;
        }

        public PivotItem CreateBugtrackerTab()
        {
            PivotItem pivotItem;
            initPivotItem("Bugtracker Stats", "\uE868", out pivotItem);
            pivotItem.DataContext = svm;
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            BugtrackerStats td = new BugtrackerStats();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            return pivotItem;
        }

        public PivotItem CreateTasksTab()
        {
            PivotItem pivotItem;
            initPivotItem("Tasks Stats", "\uE862", out pivotItem);
            pivotItem.DataContext = svm;
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            TasksStats td = new TasksStats();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            return pivotItem;
        }

        public PivotItem CreateTalksTab()
        {
            PivotItem pivotItem;
            initPivotItem("Talks Stats", "\uE0B7", out pivotItem);
            pivotItem.DataContext = svm;
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            TalksStats td = new TalksStats();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            return pivotItem;
        }

        public PivotItem CreateCustomerAccessTab()
        {
            PivotItem pivotItem;
            initPivotItem("Customer Access Stats", "\uE241", out pivotItem);
            pivotItem.DataContext = svm;
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            CustomerAccessStats td = new CustomerAccessStats();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            return pivotItem;
        }

        public PivotItem CreateOccupationTab()
        {
            PivotItem pivotItem;
            initPivotItem("Occupation", "\uE7EF", out pivotItem);
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            TeamDashBoard td = new TeamDashBoard();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            if (dvm.OccupationList != null)
                this.dvm.NotifyPropertyChanged("OccupationList");
            return pivotItem;
        }

        public PivotItem CreateMeetingsTab()
        {
            PivotItem pivotItem;
            initPivotItem("Meetings", "\uE616", out pivotItem);
            pivotItem.HorizontalContentAlignment = HorizontalAlignment.Stretch;
            pivotItem.VerticalContentAlignment = VerticalAlignment.Stretch;
            MeetingDashBoardPanel mdp = new MeetingDashBoardPanel();
            mdp.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = mdp;
            if (dvm.OccupationList != null)
                this.dvm.NotifyPropertyChanged("MeetingList");
            return pivotItem;
        }

        private async void ModularSettings_Click(object sender, RoutedEventArgs e)
        {
            var modularDialog = new ModularDashboard(DashBoardViewModel.ModularList);
            await modularDialog.ShowAsync();
            DashBoardViewModel.ModularList = modularDialog.Modulars.ToList();
            InitializePivot();
        }
    }
}