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

        public TimelineMessageView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Disabled;
        }

        //Required for navigation

        #region NavigationHelper

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

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
        }

        #endregion NavigationHelper

        private async void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                await vm.updateComment(vm.CommentSelected);
            }
        }

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

        private void OnChatViewContainerContentChanging(ListViewBase sender, ContainerContentChangingEventArgs args)
        {
            if (args.InRecycleQueue) return;
            TimelineModel message = (TimelineModel)args.Item;
            args.ItemContainer.HorizontalAlignment = message.IdCheck ? Windows.UI.Xaml.HorizontalAlignment.Right : Windows.UI.Xaml.HorizontalAlignment.Left;
            args.ItemContainer.Background = message.IdCheck ? (SolidColorBrush)Application.Current.Resources["OrangeGrappboxBrush"] : (SolidColorBrush)Application.Current.Resources["Grey3GrappboxBrush"];
        }
    }
}