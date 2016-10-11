using GrappBox.ViewModel;
using System;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.Core;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.UI;
using GrappBox.Resources;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Popups;
using Windows.Foundation.Metadata;
using Windows.UI.ViewManagement;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class UserView : Page
    {
        CoreApplicationView view;
        String ImagePath;
        bool isPasswordVisible = false;
        string password = "";
        string oldPwd = "";
        UserSettingsViewModel vm = UserSettingsViewModel.GetViewModel();

        //Required for navigation
        private readonly NavigationHelper navigationHelper;

        static private UserView instance = null;
        static public UserView GetUser()
        {
            return instance;
        }

        public UserView()
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
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["RedGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }

            this.navigationHelper.OnNavigatedTo(e);
            await vm.getAPI();
            //await vm.getProjectLogo();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private async void Update_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            if (password != "")
            {
                await vm.updateAPI(password, oldPwd);
                password = "";
                oldPwd = "";
                oldPassword.Password = "";
                newPassword.Password = "";
                retypePassword.Password = "";
            }
            else
                await vm.updateAPI();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private void Password_Click(object sender, RoutedEventArgs e)
        {
            if (isPasswordVisible == false)
            {
                PasswordStackPanel.Visibility = Visibility.Visible;
                isPasswordVisible = true;
            }
            else
            {
                string oldpwd = SettingsManager.getOption<string>("password");
                if (newPassword.Password == retypePassword.Password && oldpwd == oldPassword.Password && newPassword.Password != oldpwd && newPassword.Password.Length > 0)
                {
                    oldPassword.BorderBrush = new SolidColorBrush();
                    newPassword.BorderBrush = new SolidColorBrush();
                    retypePassword.BorderBrush = new SolidColorBrush();
                    PasswordStackPanel.Visibility = Visibility.Collapsed;
                    password = newPassword.Password;
                    oldPwd = oldpwd;
                    isPasswordVisible = false;
                    affMessage(false, "Password will be changed on update");
                }
                if (oldpwd != oldPassword.Password)
                {
                    oldPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "The old password doesn't match");
                }
                else
                    oldPassword.BorderBrush = new SolidColorBrush();
                if (newPassword.Password != retypePassword.Password)
                {
                    newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    retypePassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "New Password and Retype Password must be the same");
                }
                else
                {
                    newPassword.BorderBrush = new SolidColorBrush();
                    retypePassword.BorderBrush = new SolidColorBrush();
                }
                if (oldpwd == newPassword.Password)
                {
                    oldPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "The old password can't be the same as the new one");
                }
                if (newPassword.Password.Length == 0)
                {
                    newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    retypePassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "Your password can't be empty");
                }
                else
                {
                    newPassword.BorderBrush = new SolidColorBrush();
                    retypePassword.BorderBrush = new SolidColorBrush();
                }
            }
        }

        private async void Img_Click(object sender, RoutedEventArgs e)
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

            await filePicker.PickSingleFileAsync();
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
                vm.avatar = newAvatar;
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

        public async void affMessage(bool isError, string message)
        {
            if (isError == true)
            {
                MessageDialog msgbox = new MessageDialog(message);
                await msgbox.ShowAsync();
            }
            else
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = message;
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
        }

        private void MenuButton_Click(object sender, RoutedEventArgs e)
        {
            Menu.IsPaneOpen = !Menu.IsPaneOpen;
        }
    }
}
