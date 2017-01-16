using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using Windows.ApplicationModel.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;
using Windows.UI.Core;
using Windows.Foundation.Metadata;
using Windows.UI.ViewManagement;
using Windows.UI;
using Windows.UI.Xaml.Media;
using Grappbox.Helpers;
using Grappbox.CustomControls;
using Grappbox.HttpRequest;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// Bugtracker view page
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.Page" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class BugtrackerView : Page
    {
        CoreApplicationView view;
        BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="BugtrackerView"/> class.
        /// </summary>
        public BugtrackerView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Required;
        }

        #region NavigationHelper        
        /// <summary>
        /// Invoked when the Page is loaded and becomes the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the pending navigation that will load the current Page. Usually the most relevant property to examine is Parameter.</param>
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            if (vm != null)
                NotificationManager.NotificationChannel.PushNotificationReceived += vm.OnPushNotification;

            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["PurpleGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("PurpleGrappboxBrush"));
            dialog.ShowAsync();

            vm.OpenSelect = null;
            OpenListView.SelectedItem = null;
            CloseListView.SelectedItem = null;
            YoursListView.SelectedItem = null;
            vm.CloseSelect = null;

            await vm.getOpenTickets();
            await vm.getClosedTickets();
            await vm.getYoursTickets();
            await vm.getTagList();
            await vm.getUsers();

            dialog.Hide();
        }

        /// <summary>
        /// Invoked immediately after the Page is unloaded and is no longer the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the navigation that has unloaded the current Page.</param>
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            if (vm != null)
                NotificationManager.NotificationChannel.PushNotificationReceived -= vm.OnPushNotification;
        }
        #endregion

        /// <summary>
        /// Handles the Click event of the AddBug control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void AddBug_Click(object sender, RoutedEventArgs e)
        {
            vm.newModel();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView), null));
        }

        /// <summary>
        /// Handles the Click event of the CloseBug control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void CloseBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.OpenSelect != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("PurpleGrappboxBrush"));
                dialog.ShowAsync();

                await vm.closeTicket();
                CloseBug.Visibility = Visibility.Collapsed;

                dialog.Hide();
            }
        }

        /// <summary>
        /// Handles the Click event of the ReopenBug control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void ReopenBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.CloseSelect != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("PurpleGrappboxBrush"));
                dialog.ShowAsync();

                await vm.reopenTicket();
                ReopenBug.Visibility = Visibility.Collapsed;

                dialog.Hide();
            }
        }

        /// <summary>
        /// Handles the SelectionChanged event of the openListView control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="SelectionChangedEventArgs"/> instance containing the event data.</param>
        private void openListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListView).SelectedItem != null)
            {
                vm.OpenSelect = (sender as ListView).SelectedItem as BugtrackerModel;
                CloseBug.Visibility = Visibility.Visible;
            }
            else
                CloseBug.Visibility = Visibility.Collapsed;
        }

        /// <summary>
        /// Handles the SelectionChanged event of the closeListView control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="SelectionChangedEventArgs"/> instance containing the event data.</param>
        private void closeListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListView).SelectedItem != null)
            {
                vm.CloseSelect = (sender as ListView).SelectedItem as BugtrackerModel;
                ReopenBug.Visibility = Visibility.Visible;
            }
            else
                ReopenBug.Visibility = Visibility.Collapsed;
        }

        /// <summary>
        /// Handles the SelectionChanged event of the Pivot control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="SelectionChangedEventArgs"/> instance containing the event data.</param>
        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;
            vm.OpenSelect = null;
            OpenListView.SelectedItem = null;
            CloseListView.SelectedItem = null;
            YoursListView.SelectedItem = null;
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
            else if (num == 2)
            {
                EditOpenBug.Visibility = Visibility.Visible;
                EditCloseBug.Visibility = Visibility.Collapsed;
            }
        }

        /// <summary>
        /// Handles the Click event of the EditOpenBug control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void EditOpenBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.OpenSelect != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("PurpleGrappboxBrush"));
                dialog.ShowAsync();

                vm.getTicket(vm.OpenSelect);
                await vm.getComments();

                dialog.Hide();
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView), vm.OpenSelect.Id));
            }
        }

        /// <summary>
        /// Handles the Click event of the EditCloseBug control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void EditCloseBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.CloseSelect != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("PurpleGrappboxBrush"));
                dialog.ShowAsync();

                vm.getTicket(vm.CloseSelect);
                await vm.getComments();

                dialog.Hide();
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView), vm.CloseSelect.Id));
            }
        }
    }
}
