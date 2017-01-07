using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using Windows.Foundation.Metadata;
using Windows.UI;
using Windows.UI.Popups;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class BugView : Page
    {
        BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();
        bool isAdd;
        public BugView()
        {
            this.InitializeComponent();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Disabled;
        }

        //Required for navigation
        #region NavigationHelper

        /// <summary>
        /// The methods provided in this section are simply used to allow
        /// NavigationHelper to respond to the page's navigation methods.
        /// <para>
        /// Page specific logic should be placed in event handlers for the  
        /// <see cref="NavigationHelper.LoadState"/>
        /// and <see cref="NavigationHelper.SaveState"/>.
        /// The navigation parameter is available in the LoadState method 
        /// in addition to page state preserved during an earlier session.
        /// </para>
        /// </summary>
        /// <param name="e">Provides data for navigation methods and event
        /// handlers that cannot cancel the navigation request.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
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
            if (e.Parameter != null)
            {
                isAdd = false;
                Pivot.IsLocked = false;
            }
            else
            {
                isAdd = true;
                Comments.IsEnabled = false;
                Tags.IsEnabled = false;
                Pivot.IsLocked = true;

                SaveBug.Visibility = Visibility.Visible;
                CB.Visibility = Visibility.Visible;
                AddTag.Visibility = Visibility.Collapsed;
            }
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            if (vm != null)
                NotificationManager.NotificationChannel.PushNotificationReceived -= vm.OnPushNotification;
            vm.Title = string.Empty;
            vm.Description = string.Empty;
            Title.Text = string.Empty;
            Description.Text = string.Empty;
            CommentDescription.Text = string.Empty;
        }
        #endregion

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;

            if (num == 0)
            {
                CB.Visibility = Visibility.Visible;
                SaveBug.Visibility = Visibility.Visible;
                AddTag.Visibility = Visibility.Collapsed;
            }
            else if (num == 1)
            {
                CB.Visibility = Visibility.Collapsed;
                SaveBug.Visibility = Visibility.Collapsed;
                AddTag.Visibility = Visibility.Collapsed;
            }
            else if (num == 2)
            {
                CB.Visibility = Visibility.Visible;
                SaveBug.Visibility = Visibility.Collapsed;
                AddTag.Visibility = Visibility.Visible;
            }
        }

        #region Bug
        private async void SaveBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.Title != "" && vm.Description != "")
            {
                if (isAdd == true)
                {
                    if (await vm.addBug())
                    {
                        Comments.IsEnabled = true;
                        Tags.IsEnabled = true;
                        Pivot.IsLocked = false;
                    }
                }
                else
                {
                    await vm.editBug();
                }
            }
        }
        #endregion

        #region Tag
        private void checkBox_Loaded(object sender, RoutedEventArgs e)
        {
            if (vm.Tags != null)
            {
                int id = ((sender as CheckBox).DataContext as TagModel).Id;

                foreach (var item in vm.Tags)
                {
                    if (id == item.Id)
                    {
                        (sender as CheckBox).IsChecked = true;
                    }
                }
            }
        }
        private async void checkBox_Checked(object sender, RoutedEventArgs e)
        {
            TagModel model = (sender as CheckBox).DataContext as TagModel;

            if (model != null)
            {
                await vm.assignTag(model);
            }
        }

        private async void checkBox_Unchecked(object sender, RoutedEventArgs e)
        {
            TagModel model = (sender as CheckBox).DataContext as TagModel;

            if (model != null)
            {
                await vm.removeAssignTag(model);
            }
        }

        private async void AddTag_Click(object sender, RoutedEventArgs e)
        {
            BugTagContentDialog dialog = new BugTagContentDialog(null);
            await dialog.ShowAsync();
        }

        private async void tagListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.TagSelect = (sender as ListView).SelectedItem as TagModel;
            BugTagContentDialog dialog = new BugTagContentDialog((sender as ListView).SelectedItem as TagModel);
            await dialog.ShowAsync();
            (sender as ListView).SelectedItem = null;
        }
        #endregion

        #region User
        private void userCheckBox_Checked(object sender, RoutedEventArgs e)
        {
            UserModel model = (sender as CheckBox).DataContext as UserModel;

            if (model != null)
            {
                vm.ToAdd.Add(model.Id);
                vm.ToRemove.Remove(model.Id);
            }
        }

        private void userCheckBox_Loaded(object sender, RoutedEventArgs e)
        {
            if (vm.Users != null)
            {
                foreach (var item in vm.Users)
                {
                    if ((sender as CheckBox).Content.ToString() == item.FullName)
                    {
                        (sender as CheckBox).IsChecked = true;
                    }
                }
            }
            if (isAdd == true)
            {
                (sender as CheckBox).IsChecked = false;
            }
        }

        private void userCheckBox_Unchecked(object sender, RoutedEventArgs e)
        {
            UserModel model = (sender as CheckBox).DataContext as UserModel;

            if (model != null)
            {
                vm.ToAdd.Remove(model.Id);
                vm.ToRemove.Add(model.Id);
            }
        }
        #endregion

        #region Comment
        private async void EditComment_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("PurpleGrappboxBrush"));
            dialog.ShowAsync();

            if ((sender as Button).DataContext as BugtrackerModel != null)
                await vm.editComment((sender as Button).DataContext as BugtrackerModel);

            CommentDescription.Text = string.Empty;

            dialog.Hide();
        }

        private async void DeleteComment_Click(object sender, RoutedEventArgs e)
        {
            if ((sender as Button).DataContext as BugtrackerModel != null)
                await vm.deleteComment((sender as Button).DataContext as BugtrackerModel);
        }

        private async void PostComment_Click(object sender, RoutedEventArgs e)
        {
            if (!string.IsNullOrEmpty(CommentDescription.Text))
                await vm.addComment(CommentDescription.Text);
        }
        #endregion

        private async void checkBox_Click(object sender, RoutedEventArgs e)
        {
            TagModel model = (sender as CheckBox).DataContext as TagModel;
            bool isInTags = false;

            if (model != null)
            {
                foreach (var item in vm.Tags)
                {
                    if (model.Id == item.Id)
                    {
                        await vm.removeAssignTag(model);
                        isInTags = true;
                        break;
                    }
                }
                if (isInTags == false)
                    await vm.assignTag(model);
            }
        }

        private void OnChatViewContainerContentChanging(ListViewBase sender, ContainerContentChangingEventArgs args)
        {
            if (args.InRecycleQueue) return;
            BugtrackerModel message = (BugtrackerModel)args.Item;
            args.ItemContainer.HorizontalAlignment = message.IdCheck ? Windows.UI.Xaml.HorizontalAlignment.Right : Windows.UI.Xaml.HorizontalAlignment.Left;
            args.ItemContainer.Background = message.IdCheck ? (SolidColorBrush)Application.Current.Resources["PurpleGrappboxBrush"] : (SolidColorBrush)Application.Current.Resources["Grey3GrappboxBrush"];
        }
    }
}
