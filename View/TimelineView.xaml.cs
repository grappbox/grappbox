using Grappbox.CustomControls;
using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using Windows.ApplicationModel.Core;
using Windows.Foundation.Metadata;
using Windows.UI;
using Windows.UI.Core;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.Helpers;

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

        /// <summary>
        /// Initializes a new instance of the <see cref="TimelineView"/> class.
        /// </summary>
        public TimelineView()
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

        /// <summary>
        /// Invoked immediately after the Page is unloaded and is no longer the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the navigation that has unloaded the current Page.</param>
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            vm.TeamOffset = 0;
            vm.CustomerOffset = 0;
            vm.TeamList.Clear();
            vm.CustomerList.Clear();
        }

        #endregion NavigationHelper

        #region Selection changed        
        /// <summary>
        /// Handles the SelectionChanged event of the Pivot control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="SelectionChangedEventArgs"/> instance containing the event data.</param>
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

        /// <summary>
        /// Handles the SelectionChanged event of the TeamListView control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="SelectionChangedEventArgs"/> instance containing the event data.</param>
        private async void TeamListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListView).SelectedItem as TimelineModel != null)
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
        /// <summary>
        /// Handles the Click event of the EditMessage control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
            {
                await vm.updateMessage(vm.MessageSelected);
            }
        }

        /// <summary>
        /// Handles the Click event of the DeleteMessage control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
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

        /// <summary>
        /// Handles the Click event of the Comments control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
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

        /// <summary>
        /// Handles the Click event of the Bug control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void Bug_Click(object sender, RoutedEventArgs e)
        {
            BugtrackerViewModel bvm = BugtrackerViewModel.GetViewModel();
            if (bvm != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                dialog.ShowAsync();

                await bvm.getTagList();
                await bvm.getUsers();
                vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
                bvm.Title = vm.MessageSelected.Title;
                bvm.Description = vm.MessageSelected.Message;

                dialog.Hide();
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(BugView)));
            }
        }

        /// <summary>
        /// Handles the Click event of the AddTeam control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void AddTeam_Click(object sender, RoutedEventArgs e)
        {
            TimelineContentDialog dialog = new TimelineContentDialog(vm.TeamId);
            await dialog.ShowAsync();
        }

        /// <summary>
        /// Handles the Click event of the AddCustomer control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void AddCustomer_Click(object sender, RoutedEventArgs e)
        {
            TimelineContentDialog dialog = new TimelineContentDialog(vm.CustomerId);
            await dialog.ShowAsync();
        }
        #endregion Click

        /// <summary>
        /// Handles the GotFocus event of the TextBox control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void TextBox_GotFocus(object sender, RoutedEventArgs e)
        {
            CB.Visibility = Visibility.Collapsed;
        }

        /// <summary>
        /// Handles the LostFocus event of the TextBox control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void TextBox_LostFocus(object sender, RoutedEventArgs e)
        {
            CB.Visibility = Visibility.Visible;
        }
    }
}