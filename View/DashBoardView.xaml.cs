using GrappBox.CustomControls;
using GrappBox.Helpers;
using GrappBox.Model;
using GrappBox.ViewModel;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Graphics.Display;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class DashBoardView : Page
    {
        private PivotItem team;
        private PivotItem meetings;
        private DashBoardViewModel dvm;

        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = DashBoardViewModel.GetViewModel();
            this.NavigationCacheMode = NavigationCacheMode.Required;

            team_cb.IsChecked = SettingsManager.getOption<bool>("team_cb");
            meetings_cb.IsChecked = SettingsManager.getOption<bool>("meetings_cb");
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
            SettingsPopUp.Visibility = Visibility.Collapsed;
            SettingsPopUp.IsOpen = true;
            db_pivot.IsEnabled = true;
            DisplayInformation.AutoRotationPreferences = DisplayOrientations.Portrait;
            this.dvm = DashBoardViewModel.GetViewModel();
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;
            team = CreateOccupationTab();
            meetings = CreateMeetingsTab();
            this.db_pivot.Items.Clear();
            this.db_pivot.Items.Add(this.team);
            this.db_pivot.Items.Add(this.meetings);
            team_cb.IsChecked = true;
            meetings_cb.IsChecked = true;
            await this.dvm.InitialiseAsync();
            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void team_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("team_cb", team_cb.IsChecked);
            if (team_cb.IsChecked == true)
            {
                if (db_pivot.Items.Where(i => i.GetType() == typeof(TeamDashBoard)).FirstOrDefault() == null)
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
                if (db_pivot.Items.Where(i => i.GetType() == typeof(MeetingDashBoardPanel)).FirstOrDefault() == null)
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
            if (dvm.OccupationList != null)
                this.dvm.NotifyPropertyChanged("OccupationList");
            return pivotItem;
        }

        public PivotItem CreateMeetingsTab()
        {
            PivotItem pivotItem;
            initPivotItem("Meetings", out pivotItem);
            MeetingDashBoardPanel mdp = new MeetingDashBoardPanel();
            pivotItem.Content = mdp;
            if (dvm.OccupationList != null)
                this.dvm.NotifyPropertyChanged("MeetingList");
            return pivotItem;
        }

        private void Settings_Click(object sender, RoutedEventArgs e)
        {
            SettingsPopUp.Visibility = Visibility.Visible;
            db_pivot.IsEnabled = false;
        }

        private void CloseSettings_Click(object sender, RoutedEventArgs e)
        {
            SettingsPopUp.Visibility = Visibility.Collapsed;
            db_pivot.IsEnabled = true;
        }
    }
}