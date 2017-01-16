using Grappbox.Model;
using Grappbox.ViewModel;
using Windows.ApplicationModel.Core;
using Windows.Foundation.Metadata;
using Windows.UI;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.CustomControls;
using Grappbox.Helpers;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class TimelineMessageView : Page
    {
        private CoreApplicationView view;
        private TimelineViewModel vm = TimelineViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="TimelineMessageView"/> class.
        /// </summary>
        public TimelineMessageView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Disabled;
        }

        //Required for navigation

        #region NavigationHelper
        /// <summary>
        /// Invoked when the Page is loaded and becomes the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the pending navigation that will load the current Page. Usually the most relevant property to examine is Parameter.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
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
        }

        /// <summary>
        /// Invoked immediately after the Page is unloaded and is no longer the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the navigation that has unloaded the current Page.</param>
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
        }
        #endregion NavigationHelper    
            
        /// <summary>
        /// Handles the Click event of the EditMessage control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                await vm.updateComment(vm.CommentSelected, vm.MessageSelected.TimelineId);
            }
        }

        /// <summary>
        /// Handles the Click event of the DeleteMessage control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                dialog.ShowAsync();

                await vm.removeComment(vm.CommentSelected);

                dialog.Hide();
            }
        }

        /// <summary>
        /// Handles the Click event of the PostComment control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void PostComment_Click(object sender, RoutedEventArgs e)
        {
            if (MessageTextBox.Text != "")
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("OrangeGrappboxBrush"));
                dialog.ShowAsync();

                await vm.postComment(vm.MessageSelected.TimelineId, MessageTextBox.Text, vm.MessageSelected.Id);
                MessageTextBox.Text = "";

                dialog.Hide();
            }
        }

        /// <summary>
        /// Called when [chat view container content changing].
        /// </summary>
        /// <param name="sender">The sender.</param>
        /// <param name="args">The <see cref="ContainerContentChangingEventArgs"/> instance containing the event data.</param>
        private void OnChatViewContainerContentChanging(ListViewBase sender, ContainerContentChangingEventArgs args)
        {
            if (args.InRecycleQueue) return;
            TimelineModel message = (TimelineModel)args.Item;
            args.ItemContainer.HorizontalAlignment = message.IdCheck ? Windows.UI.Xaml.HorizontalAlignment.Right : Windows.UI.Xaml.HorizontalAlignment.Left;
            args.ItemContainer.Background = message.IdCheck ? (SolidColorBrush)Application.Current.Resources["OrangeGrappboxBrush"] : (SolidColorBrush)Application.Current.Resources["Grey3GrappboxBrush"];
        }
    }
}