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
        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = DashBoardViewModel.GetViewModel();
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
            DisplayInformation.AutoRotationPreferences = DisplayOrientations.Portrait;
            this.dvm = DashBoardViewModel.GetViewModel();
            await this.dvm.InitialiseAsync();
            team = CreateOccupationTab();
            meetings = CreateMeetingsTab();
            if (team_cb.IsChecked == true)
                this.db_pivot.Items.Add(this.team);
            if (meetings_cb.IsChecked == true)
                this.db_pivot.Items.Add(this.meetings);
        }

        private async void team_cb_Checked(object sender, RoutedEventArgs e)
        {
            SettingsManager.setOption("team_cb", team_cb.IsChecked);
            if (team_cb.IsChecked == true)
            {
                db_pivot.Items.Add(team);
                await this.dvm.InitialiseAsync();
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
                await this.dvm.InitialiseAsync();
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