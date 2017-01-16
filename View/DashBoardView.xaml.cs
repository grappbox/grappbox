using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.ViewModel;
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
    /// Project Dashboard page view
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.Page" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
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

        /// <summary>
        /// Initializes a new instance of the <see cref="DashBoardView"/> class.
        /// </summary>
        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = dvm;
            team = new PivotItem();
            meetings = new PivotItem();
        }

        /// <summary>
        /// Invoked when the Page is loaded and becomes the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the pending navigation that will load the current Page. Usually the most relevant property to examine is Parameter.</param>
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
            InitializePivot();
            loader.Hide();
        }


        /// <summary>
        /// Initializes the pivot.
        /// </summary>
        private void InitializePivot()
        {
            DbPivot?.Items?.Clear();
            foreach (var m in DashBoardViewModel.ModularList)
            {
                if (m.Selected == true)
                {
                    switch (m.DisplayName)
                    {
                        case "Occupation":
                            team = CreateOccupationTab();
                            if (!DbPivot.Items.Contains(this.team))
                                DbPivot.Items.Add(this.team);
                            break;
                        case "Meeting":
                            meetings = CreateMeetingsTab();
                            if (!DbPivot.Items.Contains(this.meetings))
                                DbPivot.Items.Add(this.meetings);
                            break;
                        case "Project Stats":
                            projectStats = CreateProjectTab();
                            if (!DbPivot.Items.Contains(this.projectStats))
                                DbPivot.Items.Add(this.projectStats);
                            break;
                        case "Bugtracker Stats":
                            bugtrackerStats = CreateBugtrackerTab();
                            if (!DbPivot.Items.Contains(this.bugtrackerStats))
                                DbPivot.Items.Add(this.bugtrackerStats);
                            break;
                        case "Tasks Stats":
                            tasksStats = CreateTasksTab();
                            if (!DbPivot.Items.Contains(this.tasksStats))
                                DbPivot.Items.Add(this.tasksStats);
                            break;
                        case "Talks Stats":
                            talksStats = CreateTalksTab();
                            if (!DbPivot.Items.Contains(this.talksStats))
                                DbPivot.Items.Add(this.talksStats);
                            break;
                        case "Customer Access Stats":
                            customerAccessStats = CreateCustomerAccessTab();
                            if (!DbPivot.Items.Contains(this.customerAccessStats))
                                DbPivot.Items.Add(this.customerAccessStats);
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        /// <summary>
        /// Initializes the pivot item.
        /// </summary>
        /// <param name="label">The pivot label.</param>
        /// <param name="glyph">The pivot glyph.</param>
        /// <param name="pivotItem">The pivot item.</param>
        private void initPivotItem(string label, string glyph, out PivotItem pivotItem)
        {
            pivotItem = new PivotItem();
            pivotItem.Header = new TabHeader(label, glyph);
        }

        /// <summary>
        /// Creates the project tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Project Stats Tab</returns>
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

        /// <summary>
        /// Creates the bugtracker tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Bug Stats Tab</returns>
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

        /// <summary>
        /// Creates the tasks tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Tasks Stats Tab</returns>
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

        /// <summary>
        /// Creates the talks tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Talks Stats Tab</returns>
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

        /// <summary>
        /// Creates the customer access tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Customer access Stats Tab</returns>
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

        /// <summary>
        /// Creates the occupation tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Occupation Stats Tab</returns>
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

        /// <summary>
        /// Creates the meetings tab.
        /// </summary>
        /// <returns>Return a new PivotItem containing the Meeting Stats Tab</returns>
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

        /// <summary>
        /// Handles the Click event of the ModularSettings control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void ModularSettings_Click(object sender, RoutedEventArgs e)
        {
            var modularDialog = new ModularDashboard(DashBoardViewModel.ModularList);
            await modularDialog.ShowAsync();
            DashBoardViewModel.ModularList = modularDialog.Modulars.ToList();
            InitializePivot();
        }
    }
}