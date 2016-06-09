using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using GrappBox.ViewModel;
using Windows.Storage.Pickers;
using Windows.ApplicationModel.Core;
using Windows.ApplicationModel.Activation;
using Windows.Storage;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using System.Diagnostics;
using GrappBox.Model;
using System.Collections.ObjectModel;
using Windows.UI.Popups;
using GrappBox.Resources;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class ProjectSettingsView : Page
    {
        CoreApplicationView view;
        String ImagePath;
        ProjectSettingsViewModel vm = ProjectSettingsViewModel.GetViewModel();
        bool isMoreClicked = false;
        DateTime defaultDate = DateTime.MinValue;

        //Required for navigation
        private readonly NavigationHelper navigationHelper;

        static private ProjectSettingsView instance = null;
        static public ProjectSettingsView GetInstance()
        {
            return instance;
        }
        public ProjectSettingsView()
        {
            this.InitializeComponent();
            instance = this;
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
        /// <summary>
        /// Gets the <see cref="NavigationHelper"/> associated with this <see cref="Page"/>.
        /// </summary>
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }

        /// <summary>
        /// Populates the page with content passed during navigation. Any saved state is also
        /// provided when recreating a page from a prior session.
        /// </summary>
        /// <param name="sender">
        /// The source of the event; typically <see cref="NavigationHelper"/>.
        /// </param>
        /// <param name="e">Event data that provides both the navigation parameter passed to
        /// <see cref="Frame.Navigate(Type, Object)"/> when this page was initially requested and
        /// a dictionary of state preserved by this page during an earlier
        /// session. The state will be null the first time a page is visited.</param>
        private void NavigationHelper_LoadState(object sender, LoadStateEventArgs e)
        {

        }

        /// <summary>
        /// Preserves state associated with this page in case the application is suspended or the
        /// page is discarded from the navigation cache. Values must conform to the serialization
        /// requirements of <see cref="SuspensionManager.SessionState"/>.
        /// </summary>
        /// <param name="sender">The source of the event; typically <see cref="NavigationHelper"/>.</param>
        /// <param name="e">Event data that provides an empty dictionary to be populated with
        /// serializable state.</param>
        private void NavigationHelper_SaveState(object sender, SaveStateEventArgs e)
        {

        }

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
            this.navigationHelper.OnNavigatedTo(e);
            vm.getProjectSettings();
            vm.getProjectUsers();
            vm.getCustomerAccesses();
            vm.getRoles();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        #region imgClicked
        private void Img_Click(object sender, RoutedEventArgs e)
        {
            ImagePath = string.Empty;
            FileOpenPicker filePicker = new FileOpenPicker();
            filePicker.SuggestedStartLocation = PickerLocationId.PicturesLibrary;
            filePicker.ViewMode = PickerViewMode.Thumbnail;

            // Filter to include a sample subset of file types
            filePicker.FileTypeFilter.Clear();
            filePicker.FileTypeFilter.Add(".bmp");
            filePicker.FileTypeFilter.Add(".png");
            filePicker.FileTypeFilter.Add(".jpeg");
            filePicker.FileTypeFilter.Add(".jpg");

            filePicker.PickSingleFileAndContinue();
            view.Activated += viewActivated;
        }

        private async void viewActivated(CoreApplicationView sender, IActivatedEventArgs args1)
        {
            FileOpenPickerContinuationEventArgs args = args1 as FileOpenPickerContinuationEventArgs;

            if (args != null)
            {
                if (args.Files.Count == 0) return;

                view.Activated -= viewActivated;
                StorageFile storageFile = args.Files[0];
                var stream = await storageFile.OpenAsync(Windows.Storage.FileAccessMode.Read);
                var bitmapImage = new Windows.UI.Xaml.Media.Imaging.BitmapImage();
                await bitmapImage.SetSourceAsync(stream);

                var decoder = await Windows.Graphics.Imaging.BitmapDecoder.CreateAsync(stream);
                img.Source = bitmapImage;

                //For Convert Bitmap Image to Base64
                string newAvatar = await StorageFileToBase64(args.Files[0]);
                vm.logo = newAvatar;
            }
        }

        private async Task<string> StorageFileToBase64(StorageFile file)
        {
            string Base64String = "";

            if (file != null)
            {
                IRandomAccessStream fileStream = await file.OpenAsync(FileAccessMode.Read);
                var reader = new DataReader(fileStream.GetInputStreamAt(0));
                await reader.LoadAsync((uint)fileStream.Size);
                byte[] byteArray = new byte[fileStream.Size];
                reader.ReadBytes(byteArray);
                Base64String = Convert.ToBase64String(byteArray);
            }

            return Base64String;
        }
        #endregion imgClicked

        private void ProjectSettingsUpdate_Click(object sender, RoutedEventArgs e)
        {
            vm.updateProjectSettings();
        }

        private void AddUser_Click(object sender, RoutedEventArgs e)
        {
            vm.addProjectUser(UserMail.Text);
        }

        private void RemoveUserButton_Click(object sender, RoutedEventArgs e)
        {
            vm.removeProjectUser();
        }

        private void Delete_Click(object sender, RoutedEventArgs e)
        {
            if (DateTime.Equals(vm.DeletedAt, defaultDate) == false)
            {
                //retreive
                vm.retrieveProject();
                vm.getProjectSettings();
            }
            else
            {
                //delete
                vm.deleteProject();
                vm.getProjectSettings();
            }
        }

        private void More_Click(object sender, RoutedEventArgs e)
        {
            if (isMoreClicked == false)
            {
                MoreStackPanel.Visibility = Visibility.Visible;
                isMoreClicked = true;
                MoreButton.Content = "Less";
                if (DateTime.Equals(vm.DeletedAt, defaultDate) == false)
                {
                    DeleteDate.Visibility = Visibility.Visible;
                    DeleteDate.Text = "Your project will be deleted at " + vm.DeletedAt.ToString("yyyy-MM-dd hh:mm:ss");
                    DeleteButton.Content = "Retreive Project";
                }
                else
                {
                    DeleteDate.Visibility = Visibility.Collapsed;
                    DeleteButton.Content = "Delete Project";
                }
            }
            else
            {
                MoreStackPanel.Visibility = Visibility.Collapsed;
                MoreButton.Content = "More";
                isMoreClicked = false;
            }
        }

        private void userListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.UserSelected = (sender as ListBox).SelectedItem as ProjectUserModel;
        }

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;

            if (num == 1)
            {
                CB.Visibility = Visibility.Visible;
                RemoveUser.Visibility = Visibility.Visible;
                RemoveCU.Visibility = Visibility.Collapsed;
                RegenerateCu.Visibility = Visibility.Collapsed;
                RemoveRole.Visibility = Visibility.Collapsed;
                ModifyRole.Visibility = Visibility.Collapsed;
            }
            else if (num == 2)
            {
                CB.Visibility = Visibility.Visible;
                RemoveUser.Visibility = Visibility.Collapsed;
                RemoveCU.Visibility = Visibility.Visible;
                RegenerateCu.Visibility = Visibility.Visible;
                RemoveRole.Visibility = Visibility.Collapsed;
                ModifyRole.Visibility = Visibility.Collapsed;
            }
            else if (num == 3)
            {
                CB.Visibility = Visibility.Visible;
                RemoveUser.Visibility = Visibility.Collapsed;
                RemoveCU.Visibility = Visibility.Collapsed;
                RegenerateCu.Visibility = Visibility.Collapsed;
                RemoveRole.Visibility = Visibility.Visible;
                ModifyRole.Visibility = Visibility.Visible;
            }
            else
            {
                CB.Visibility = Visibility.Collapsed;
            }
        }

        private void RegenerateCU_Click(object sender, RoutedEventArgs e)
        {
            vm.regenerateCustomerAccess();
        }

        private void RemoveCustomerButton_Click(object sender, RoutedEventArgs e)
        {
            vm.removeCustomerAccess();
        }

        private async void AddCU_Click(object sender, RoutedEventArgs e)
        {
            ObservableCollection<CustomerAccessModel> cuList = vm.CustomerList;
            bool exist = false;

            foreach (var item in cuList)
            {
                if (item.Name == CustomerName.Text)
                    exist = true;
            }
            if (CustomerName.Text != "" && CustomerName.Text != null && exist == false)
                vm.addCustomerAccess(CustomerName.Text);
            else
            {
                if (CustomerName.Text == "" && CustomerName.Text == null)
                {
                    MessageDialog msgbox = new MessageDialog("The name must not be empty");
                    await msgbox.ShowAsync();
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog("The name must be different from an existing one");
                    await msgbox.ShowAsync();
                }
            }
        }

        private void customerListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.CustomerSelected = (sender as ListBox).SelectedItem as CustomerAccessModel;
        }

        private void AddRole_Click(object sender, RoutedEventArgs e)
        {
            vm.getUsersAssigned(0);
            this.Frame.Navigate(typeof(RoleView), null);
        }

        private void roleListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.RoleSelected = (sender as ListBox).SelectedItem as ProjectRoleModel;
        }

        private void ModifyRole_Click(object sender, RoutedEventArgs e)
        {
            vm.getUsersAssigned(vm.RoleSelected.Id);
            this.Frame.Navigate(typeof(RoleView), vm.RoleSelected.Id);
        }

        private void RemoveRoleButton_Click(object sender, RoutedEventArgs e)
        {
            vm.removeRole();
        }
    }
}
