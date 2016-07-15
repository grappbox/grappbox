using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.Ressources;
using GrappBox.ViewModel;
using System;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;

namespace GrappBox.View
{
    public sealed partial class GenericDahsboard : Page
    {
        private NavigationHelper navigationHelper;
        Frame frame = Window.Current.Content as Frame;
        public GenericDahsboard()
        {
            this.InitializeComponent();
            this.DataContext = new GenericDashboardViewModel();

            this.NavigationCacheMode = NavigationCacheMode.Required;
            this.navigationHelper = new NavigationHelper(this);
        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            GenericDashboardViewModel vmdl = this.DataContext as GenericDashboardViewModel;
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;
            if (await vmdl.getProjectList() == false)
            {
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                this.navigationHelper.GoBack();
            }
            await vmdl.getProjectsLogo();
            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }
        private async void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            ListView lv = sender as ListView;
            ProjectListModel plm = lv.SelectedItem as ProjectListModel;
            SettingsManager.setOption("ProjectIdChoosen", plm.Id);
            SettingsManager.setOption("ProjectNameChoosen", plm.Name);
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => frame.Navigate(typeof(View.DashBoardView)));
        }

        private async void CreateProject_Click(object sender, RoutedEventArgs e)
        {
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => frame.Navigate(typeof(View.ProjectSettingsView), "newProject"));
        }
    }
}
