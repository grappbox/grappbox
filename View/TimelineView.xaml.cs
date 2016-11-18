using Grappbox.CustomControls;
using Grappbox.Model;
using Grappbox.ViewModel;
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

namespace Grappbox.View
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

            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
            dialog.ShowAsync();

            vm.MessageSelected = null;
            result = await vm.getTimelines();
            if (result == false)
            {
                dialog.Hide();
                Frame.GoBack();
            }
            vm.CustomerList.Clear();
            vm.TeamList.Clear();
            result = await vm.getCustomerMessages();
            if (result == false)
            {
                dialog.Hide();
                Frame.GoBack();
            }
            result = await vm.getTeamMessages();
            dialog.Hide();
            if (result == false)
                Frame.GoBack();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            vm.TeamOffset = 0;
            vm.CustomerOffset = 0;
            vm.TeamList.Clear();
            vm.CustomerList.Clear();
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
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                dialog.ShowAsync();

                await vm.getComments(vm.MessageSelected.TimelineId, vm.MessageSelected.Id);

                dialog.Hide();
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
                await vm.updateMessage(vm.MessageSelected);
            }
        }

        private async void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                dialog.ShowAsync();

                await vm.removeMessage(vm.MessageSelected);

                dialog.Hide();
            }
        }

        private async void Comments_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                dialog.ShowAsync();

                await vm.getComments(vm.MessageSelected.TimelineId, vm.MessageSelected.Id);

                dialog.Hide();
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(TimelineMessageView)));
            }
        }

        private async void Bug_Click(object sender, RoutedEventArgs e)
        {
            //BugtrackerViewModel bvm = BugtrackerViewModel.GetViewModel();
            //if (bvm != null)
            //{
            //    var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                //dialog.ShowAsync();

            //    await bvm.getTagList();
            //    await bvm.getUsers();
            //    vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            //    bvm.Title = vm.MessageSelected.Title;
            //    bvm.Description = vm.MessageSelected.Message;

            //    dialog.Hide();
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