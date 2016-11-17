using GrappBox.CustomControls;
using GrappBox.Helpers;
using GrappBox.Model;
using GrappBox.ViewModel;
using System.Diagnostics;
using Windows.Foundation.Metadata;
using Windows.UI;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

namespace GrappBox.View
{
    public sealed partial class GenericDahsboard : Page
    {
        public GenericDahsboard()
        {
            this.InitializeComponent();
            this.DataContext = new GenericDashboardViewModel();
        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["RedGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            GenericDashboardViewModel vmdl = this.DataContext as GenericDashboardViewModel;
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();
            await vmdl.getProjectList();
            await vmdl.getProjectsLogo();
            dialog.Hide();
        }

        private void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            ListView lv = sender as ListView;
            ProjectListModel plm = lv.SelectedItem as ProjectListModel;
            AppGlobalHelper.ProjectId = plm.Id;
            AppGlobalHelper.ProjectName = plm.Name;
            Debug.WriteLine("ProjectId= {0}", plm.Id);
            Frame.Navigate(typeof(View.DashBoardView));
        }

        private void CreateProject_Click(object sender, RoutedEventArgs e)
        {
            Frame.Navigate(typeof(View.ProjectSettingsView), "newProject");
        }
    }
}