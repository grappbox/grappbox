using GrappBox.ApiCom;
using GrappBox.Ressources;
using GrappBox.View;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class LeftMenu : UserControl
    {
        Frame frame = Window.Current.Content as Frame;
        public LeftMenu()
        {
            this.InitializeComponent();
            ProjectName.Text = SettingsManager.getOption<string>("ProjectNameChoosen");
        }

        #region menuClicked
        private void WhiteboardButton_Click(object sender, RoutedEventArgs e)
        {
            frame.Navigate(typeof(WhiteBoardView));
        }

        private void UserSettingsButton_Click(object sender, RoutedEventArgs e)
        {
            UserSettingsViewModel usvm = new UserSettingsViewModel();
            usvm.getAPI();
            frame.Navigate(typeof(UserView));
        }

        private void DashboardButton_Click(object sender, RoutedEventArgs e)
        {
            frame.Navigate(typeof(DashBoardView));
        }

        private void ProjectSettingsButton_Click(object sender, RoutedEventArgs e)
        {
            ProjectSettingsViewModel psvm = new ProjectSettingsViewModel();
            psvm.getProjectSettings();
            psvm.getProjectUsers();
            psvm.getCustomerAccesses();
            psvm.getRoles();
            frame.Navigate(typeof(ProjectSettingsView));
        }

        private void BugtrackerButton_Click(object sender, RoutedEventArgs e)
        {
            BugtrackerViewModel vm = new BugtrackerViewModel();
            vm.getOpenTickets();
            vm.getClosedTickets();
            vm.getStateList();
            vm.getTagList();
            vm.getUsers();
            frame.Navigate(typeof(BugtrackerView));
        }
        private void TimelineButton_Click(object sender, RoutedEventArgs e)
        {
            frame.Navigate(typeof(TimelineView));
        }
        private void CloudButton_Click(object sender, RoutedEventArgs e)
        {
            CloudViewModel vm = new CloudViewModel();
            vm.getLS();
            frame.Navigate(typeof(CloudView));
        }
        #endregion menuClicked

        private void Logoututton_Click(object sender, RoutedEventArgs e)
        {
            logout();
        }

        private async void logout()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "accountadministration/logout");
            if (res.IsSuccessStatusCode)
            {
                frame.Navigate(typeof(MainPage));
            }
            else {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }
    }
}
