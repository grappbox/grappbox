using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.ApplicationModel.Core;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class TimelineView : Page
    {
        CoreApplicationView view;
        TimelineViewModel vm = TimelineViewModel.GetViewModel();
        List<Border> items = new List<Border>();

        //Required for navigation
        private readonly NavigationHelper navigationHelper;
        public TimelineView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;

            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
            this.navigationHelper = new NavigationHelper(this);
            this.navigationHelper.LoadState += this.NavigationHelper_LoadState;
            this.navigationHelper.SaveState += this.NavigationHelper_SaveState;
        }

        //Required for navigation
        #region NavigationHelper
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }
        private void NavigationHelper_LoadState(object sender, LoadStateEventArgs e)
        {

        }
        private void NavigationHelper_SaveState(object sender, SaveStateEventArgs e)
        {

        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            bool result = true;
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;
            
            this.navigationHelper.OnNavigatedTo(e);
            Pivot.IsLocked = false;
            PostTeamMesPopUp.Visibility = Visibility.Collapsed;
            PostTeamMesPopUp.IsOpen = true;
            TeamListView.IsEnabled = true;
            PostCustomerMesPopUp.Visibility = Visibility.Collapsed;
            PostCustomerMesPopUp.IsOpen = true;
            CustomerListView.IsEnabled = true;
            vm.MessageSelected = null;
            MessageTitle.Text = "";
            Message.Text = "";
            result = await vm.getTimelines();
            if (result == false)
            {
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                this.navigationHelper.GoBack();
            }
            result = await vm.getCustomerMessages();
            if (result == false)
            {
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                this.navigationHelper.GoBack();
            }
            result = await vm.getTeamMessages();
            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
            if (result == false)
                this.navigationHelper.GoBack();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        #region Selection changed
        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;
            vm.MessageSelected = null;

            if (num == 0)
            {
                AddTeamMessage.Visibility = Visibility.Visible;
                AddCustomerMessage.Visibility = Visibility.Collapsed;
            }
            else if (num == 1)
            {
                AddTeamMessage.Visibility = Visibility.Collapsed;
                AddCustomerMessage.Visibility = Visibility.Visible;
            }
        }
        #endregion

        #region Click
        private async void PostTeamMessage_Click(object sender, RoutedEventArgs e)
        {
            if (MessageTitle.Text != "" && Message.Text != "")
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.postMessage(vm.TeamId, MessageTitle.Text, Message.Text);
                Pivot.IsLocked = false;
                PostTeamMesPopUp.Visibility = Visibility.Collapsed;
                TeamListView.IsEnabled = true;

                MessageTitle.Text = "";
                Message.Text = "";

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void PostCustomerMessage_Click(object sender, RoutedEventArgs e)
        {
            if (CustomerTitle.Text != "" && CustomerMessage.Text != "")
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.postMessage(vm.CustomerId, CustomerTitle.Text, CustomerMessage.Text);
                Pivot.IsLocked = false;
                PostCustomerMesPopUp.Visibility = Visibility.Collapsed;
                CustomerListView.IsEnabled = true;

                CustomerTitle.Text = "";
                CustomerMessage.Text = "";

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.updateMessage(vm.MessageSelected);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.removeMessage(vm.MessageSelected);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void Comments_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.getComments(vm.MessageSelected.TimelineId, vm.MessageSelected.Id);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(TimelineMessageView)));
            }
        }

        private async void Bug_Click(object sender, RoutedEventArgs e)
        {
            BugtrackerViewModel bvm = BugtrackerViewModel.GetViewModel();
            if (bvm != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;
                
                await bvm.getTagList();
                await bvm.getUsers();
                vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
                bvm.Title = vm.MessageSelected.Title;
                bvm.Description = vm.MessageSelected.Message;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView)));
            }
        }

        private void CancelTeam_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = false;
            PostTeamMesPopUp.Visibility = Visibility.Collapsed;
            TeamListView.IsEnabled = true;
        }

        private void AddTeam_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = true;
            PostTeamMesPopUp.Visibility = Visibility.Visible;
            TeamListView.IsEnabled = false;
        }

        private void CancelCustomer_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = false;
            PostCustomerMesPopUp.Visibility = Visibility.Collapsed;
            CustomerListView.IsEnabled = true;
        }

        private void AddCustomer_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = true;
            PostCustomerMesPopUp.Visibility = Visibility.Visible;
            CustomerListView.IsEnabled = false;
        }
        #endregion

        private void PostTeamMesPopUp_Loaded(object sender, RoutedEventArgs e)
        {
            PostTeamMesPopUp.VerticalOffset = (slideInMenuContentControl.ActualHeight - (TeamStackPanel.ActualHeight * 1.5)) / 2;
        }

        private void PostCustomerMesPopUp_Loaded(object sender, RoutedEventArgs e)
        {
            PostCustomerMesPopUp.VerticalOffset = (slideInMenuContentControl.ActualHeight - (CustStackPanel.ActualHeight * 1.5)) / 2;
        }
    }
}
