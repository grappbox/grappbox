using GrappBox.Model;
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
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using GrappBox.Resources;
using Windows.UI.Core;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class BugtrackerView : Page
    {
        private NavigationHelper navigationHelper;
        CoreApplicationView view;
        BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();
        public BugtrackerView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Required;

            this.navigationHelper = new NavigationHelper(this);
        }
        #region NavigationHelper

        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            this.navigationHelper.OnNavigatedTo(e);
            vm.OpenSelect = null;
            OpenListView.SelectedItem = null;
            CloseListView.SelectedItem = null;
            vm.CloseSelect = null;

            await vm.getOpenTickets();
            await vm.getClosedTickets();
            await vm.getTagList();
            await vm.getUsers();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private async void AddBug_Click(object sender, RoutedEventArgs e)
        {
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView), null));
        }

        private async void CloseBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.OpenSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.closeTicket();
                CloseBug.Visibility = Visibility.Collapsed;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void ReopenBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.CloseSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.reopenTicket();
                ReopenBug.Visibility = Visibility.Collapsed;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private void openListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListBox).SelectedItem != null)
            {
                vm.OpenSelect = (sender as ListBox).SelectedItem as BugtrackerModel;
                CloseBug.Visibility = Visibility.Visible;
            }
            else
                CloseBug.Visibility = Visibility.Collapsed;
        }

        private void closeListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListBox).SelectedItem != null)
            {
                vm.CloseSelect = (sender as ListBox).SelectedItem as BugtrackerModel;
                ReopenBug.Visibility = Visibility.Visible;
            }
            else
                ReopenBug.Visibility = Visibility.Collapsed;
        }

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;
            vm.OpenSelect = null;
            OpenListView.SelectedItem = null;
            CloseListView.SelectedItem = null;
            vm.CloseSelect = null;

            if (num == 0)
            {
                EditOpenBug.Visibility = Visibility.Visible;
                EditCloseBug.Visibility = Visibility.Collapsed;
            }
            else if (num == 1)
            {
                EditOpenBug.Visibility = Visibility.Collapsed;
                EditCloseBug.Visibility = Visibility.Visible;
            }
        }

        private async void EditOpenBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.OpenSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                vm.getTicket(vm.OpenSelect);
                await vm.getComments();

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView), vm.OpenSelect.Id));
            }
        }

        private async void EditCloseBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.CloseSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                vm.getTicket(vm.CloseSelect);
                await vm.getComments();

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView), vm.CloseSelect.Id));
            }
        }
    }
}
