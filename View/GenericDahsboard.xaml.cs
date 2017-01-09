using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Diagnostics;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

namespace Grappbox.View
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
            GenericDashboardViewModel vmdl = this.DataContext as GenericDashboardViewModel;
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();
            await vmdl.getProjectList();
            dialog.Hide();
        }

        private void CreateProject_Click(object sender, RoutedEventArgs e)
        {
            Frame.Navigate(typeof(View.ProjectSettingsView), "newProject");
        }

        private void ListView_ItemClick(object sender, ItemClickEventArgs e)
        {
            ProjectListModel plm = e.ClickedItem as ProjectListModel;
            SessionHelper.CreateSessionHelper(plm);
            Debug.WriteLine("ProjectId= {0}", plm.Id);
            Frame.Navigate(typeof(View.DashBoardView));
        }
    }
}