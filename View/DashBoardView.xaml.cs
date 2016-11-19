using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
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

namespace Grappbox.View
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

            TeamCb.IsChecked = SettingsManager.getOption<bool>("team_cb");
            MeetingsCb.IsChecked = SettingsManager.getOption<bool>("meetings_cb");
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
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();
            DbPivot.IsEnabled = true;
            this.dvm = DashBoardViewModel.GetViewModel();
            team = CreateOccupationTab();
            meetings = CreateMeetingsTab();
            DbPivot?.Items?.Clear();
            DbPivot?.Items?.Add(this.team);
            DbPivot?.Items?.Add(this.meetings);
            TeamCb.IsChecked = true;
            MeetingsCb.IsChecked = true;
            await this.dvm.InitialiseAsync();
            dialog.Hide();
        }

        private async void team_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("team_cb", TeamCb.IsChecked);
            if (TeamCb.IsChecked == true)
            {
                if (DbPivot?.Items?.FirstOrDefault(i => i is TeamDashBoard) == null)
                    DbPivot?.Items?.Add(team);
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
                dialog.ShowAsync();
                await this.dvm.InitialiseAsync();
                dialog.Hide();
            }
            else
                DbPivot?.Items?.Remove(team);
        }

        private async void meetings_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("meetings_cb", MeetingsCb.IsChecked);
            if (MeetingsCb.IsChecked == true)
            {
                if (DbPivot?.Items?.FirstOrDefault(i => i is MeetingDashBoardPanel) == null)
                    DbPivot?.Items?.Add(meetings);
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
                dialog.ShowAsync();
                await this.dvm.InitialiseAsync();
                dialog.Hide();
            }
            else
                DbPivot?.Items?.Remove(meetings);
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
            DbPivot.IsEnabled = false;
        }

        private void CloseSettings_Click(object sender, RoutedEventArgs e)
        {
            SettingsPopUp.Visibility = Visibility.Collapsed;
            DbPivot.IsEnabled = true;
        }
    }
}