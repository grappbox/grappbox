using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.ViewModel;
using Windows.ApplicationModel.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class TimelineMessageView : Page
    {
        CoreApplicationView view;
        TimelineViewModel vm = TimelineViewModel.GetViewModel();
        
        public TimelineMessageView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            CommentsListView.ContainerContentChanging += OnChatViewContainerContentChanging;
        }

        //Required for navigation
        #region NavigationHelper

        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            
        }
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            
        }
        #endregion

        private async void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.updateMessage(vm.CommentSelected);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.removeMessage(vm.CommentSelected);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void PostComment_Click(object sender, RoutedEventArgs e)
        {
            if (MessageTextBox.Text != "")
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.postComment(vm.MessageSelected.TimelineId, MessageTextBox.Text, vm.MessageSelected.Id);
                MessageTextBox.Text = "";

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private void OnChatViewContainerContentChanging(ListViewBase sender, ContainerContentChangingEventArgs args)
        {
            if (args.InRecycleQueue) return;
            TimelineModel message = (TimelineModel)args.Item;
            args.ItemContainer.HorizontalAlignment = message.IdCheck ? Windows.UI.Xaml.HorizontalAlignment.Right : Windows.UI.Xaml.HorizontalAlignment.Left;
        }
    }
}
