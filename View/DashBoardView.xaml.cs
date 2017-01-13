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

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class DashBoardView : Page
    {
        private PivotItem team;
        private PivotItem meetings;
        private PivotItem statistics;
        private DashBoardViewModel dvm;

        public DashBoardView()
        {
            this.InitializeComponent();
            this.DataContext = DashBoardViewModel.GetViewModel();
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
            this.dvm = DashBoardViewModel.GetViewModel();
            await this.dvm.InitialiseAsync();
            team = CreateOccupationTab();
            meetings = CreateMeetingsTab();
            //statistics = CreateStatisticsTab();
            InitializePivot();
            loader.Hide();
        }

        private void InitializePivot()
        {
            DbPivot?.Items?.Clear();
            foreach (var m in DashBoardViewModel.ModularList)
            {
                if (m.DisplayName == "Occupation" && m.Selected == true)
                    DbPivot?.Items?.Add(this.team);
                if (m.DisplayName == "Meeting" && m.Selected == true)
                    DbPivot?.Items?.Add(this.meetings);
                //if (m.DisplayName == "Statistics")
                //    DbPivot?.Items?.Add();
            }
        }

        private void initPivotItem(string header, out PivotItem pivotItem)
        {
            pivotItem = new PivotItem();
            pivotItem.Header = header;
        }

        //public PivotItem CreateStatisticsTab()
        //{
        //}

        public PivotItem CreateOccupationTab()
        {
            PivotItem pivotItem;
            initPivotItem("Occupation", out pivotItem);
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
            initPivotItem("Meetings", out pivotItem);
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