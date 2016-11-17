using GrappBox.CustomControls;
using GrappBox.Model;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Threading.Tasks;
using Windows.ApplicationModel.Core;
using Windows.Foundation;
using Windows.Foundation.Metadata;
using Windows.Graphics.Display;
using Windows.UI;
using Windows.UI.Core;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Data;
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
        private CoreApplicationView view;
        private TimelineViewModel vm = TimelineViewModel.GetViewModel();

        public TimelineView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
        }

        #region NavigationHelper

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["OrangeGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }

            bool result = true;

            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            vm.MessageSelected = null;
            result = await vm.getTimelines();
            if (result == false)
            {
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                Frame.GoBack();
            }
            vm.CustomerList.Clear();
            vm.TeamList.Clear();
            result = await vm.getCustomerMessages();
            if (result == false)
            {
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                Frame.GoBack();
            }
            result = await vm.getTeamMessages();
            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
            if (result == false)
                Frame.GoBack();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            vm.TeamOffset = 0;
            vm.CustomerOffset = 0;
        }

        #endregion NavigationHelper

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

        private async void TeamListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.MessageSelected = (sender as ListView).SelectedItem as TimelineModel;
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

        #endregion Selection changed

        #region Click

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
            //BugtrackerViewModel bvm = BugtrackerViewModel.GetViewModel();
            //if (bvm != null)
            //{
            //    LoadingBar.IsEnabled = true;
            //    LoadingBar.Visibility = Visibility.Visible;

            //    await bvm.getTagList();
            //    await bvm.getUsers();
            //    vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            //    bvm.Title = vm.MessageSelected.Title;
            //    bvm.Description = vm.MessageSelected.Message;

            //    LoadingBar.IsEnabled = false;
            //    LoadingBar.Visibility = Visibility.Collapsed;
            //    await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView)));
            //}
        }

        private async void AddTeam_Click(object sender, RoutedEventArgs e)
        {
            TimelineContentDialog dialog = new TimelineContentDialog(vm.TeamId);
            await dialog.ShowAsync();
        }

        private async void AddCustomer_Click(object sender, RoutedEventArgs e)
        {
            TimelineContentDialog dialog = new TimelineContentDialog(vm.CustomerId);
            await dialog.ShowAsync();
        }

        #endregion Click
    }
}