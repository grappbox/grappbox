using GrappBox.Model;
using GrappBox.Ressources;
using GrappBox.ViewModel;
using System.Diagnostics;
using System.Linq;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;
using System.Collections.ObjectModel;
using GrappBox.CustomControler;
using Windows.Graphics.Display;
using GrappBox.Resources;

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class DashBoardView : Page
    {
        PivotItem team;
        PivotItem meetings;
        DashBoardViewModel dvm;
        private NavigationHelper navigationHelper;
        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = DashBoardViewModel.GetViewModel();
            this.NavigationCacheMode = NavigationCacheMode.Required;

            this.navigationHelper = new NavigationHelper(this);
            this.navigationHelper.LoadState += this.NavigationHelper_LoadState;
            this.navigationHelper.SaveState += this.NavigationHelper_SaveState;
            team_cb.IsChecked = SettingsManager.getOption<bool>("team_cb");
            meetings_cb.IsChecked = SettingsManager.getOption<bool>("meetings_cb");
            team = new PivotItem();
            meetings = new PivotItem();
        }

        #region NavigationHelper
        /// <summary>
        /// Gets the <see cref="NavigationHelper"/> associated with this <see cref="Page"/>.
        /// </summary>
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }

        /// <summary>
        /// Populates the page with content passed during navigation. Any saved state is also
        /// provided when recreating a page from a prior session.
        /// </summary>
        /// <param name="sender">
        /// The source of the event; typically <see cref="NavigationHelper"/>.
        /// </param>
        /// <param name="e">Event data that provides both the navigation parameter passed to
        /// <see cref="Frame.Navigate(Type, Object)"/> when this page was initially requested and
        /// a dictionary of state preserved by this page during an earlier
        /// session. The state will be null the first time a page is visited.</param>
        private void NavigationHelper_LoadState(object sender, LoadStateEventArgs e)
        {

        }

        /// <summary>
        /// Preserves state associated with this page in case the application is suspended or the
        /// page is discarded from the navigation cache. Values must conform to the serialization
        /// requirements of <see cref="SuspensionManager.SessionState"/>.
        /// </summary>
        /// <param name="sender">The source of the event; typically <see cref="NavigationHelper"/>.</param>
        /// <param name="e">Event data that provides an empty dictionary to be populated with
        /// serializable state.</param>
        private void NavigationHelper_SaveState(object sender, SaveStateEventArgs e)
        {

        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d’événement décrivant la manière dont l’utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected override async void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
            DisplayInformation.AutoRotationPreferences = DisplayOrientations.Portrait;
            this.dvm = DashBoardViewModel.GetViewModel();
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;
            await this.dvm.InitialiseAsync();
            team = CreateOccupationTab();
            meetings = CreateMeetingsTab();
            if (team_cb.IsChecked == true)
                this.db_pivot.Items.Add(this.team);
            if (meetings_cb.IsChecked == true)
                this.db_pivot.Items.Add(this.meetings);
            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void team_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("team_cb", team_cb.IsChecked);
            if (team_cb.IsChecked == true)
            {
                db_pivot.Items.Add(team);
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;
                await this.dvm.InitialiseAsync();
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
            else
                db_pivot.Items.Remove(team);
        }

        private async void meetings_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("meetings_cb", meetings_cb.IsChecked);
            if (meetings_cb.IsChecked == true)
            {
                db_pivot.Items.Add(meetings);
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;
                await this.dvm.InitialiseAsync();
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
            else
                db_pivot.Items.Remove(meetings);
        }

        private void initPivotItem(string header, out PivotItem pivotItem)
        {
            pivotItem = new PivotItem();
            pivotItem.Header = header;
        }

        public PivotItem CreateOccupationTab()
        {
            PivotItem pivotItem;
            initPivotItem("Occupation", out pivotItem);
            TeamDashBoard td = new TeamDashBoard();
            td.HorizontalAlignment = HorizontalAlignment.Stretch;
            pivotItem.Content = td;
            this.dvm.NotifyPropertyChanged("OccupationList");
            return pivotItem;
        }

        public PivotItem CreateMeetingsTab()
        {
            PivotItem pivotItem;
            initPivotItem("Meetings", out pivotItem);
            MeetingDashBoardPanel mdp = new MeetingDashBoardPanel();
            pivotItem.Content = mdp;
            this.dvm.NotifyPropertyChanged("MeetingList");
            return pivotItem;
        }
    }
}