using Grappbox.Helpers;
using Grappbox.ViewModel;
using System;
using System.Threading.Tasks;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.Core;
using Windows.Foundation.Metadata;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.Storage.Streams;
using Windows.UI;
using Windows.UI.Popups;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.CustomControls;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class UserView : Page
    {
        private CoreApplicationView view;
        private String ImagePath;
        private bool isPasswordVisible = false;
        private string password = string.Empty;
        private string oldPwd = string.Empty;
        private UserSettingsViewModel vm = UserSettingsViewModel.GetViewModel();

        static private UserView instance = null;

        /// <summary>
        /// Gets the user view instance.
        /// </summary>
        /// <returns></returns>
        static public UserView GetUser()
        {
            return instance;
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="UserView"/> class.
        /// </summary>
        public UserView()
        {
            this.InitializeComponent();
            instance = this;
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;

            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
        }

        /// <summary>
        /// Invoked when the Page is loaded and becomes the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the pending navigation that will load the current Page. Usually the most relevant property to examine is Parameter.</param>
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

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

            await vm.getAPI();
            //await vm.getProjectLogo();

            dialog.Hide();
        }

        /// <summary>
        /// Handles the Click event of the Update control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void Update_Click(object sender, RoutedEventArgs e)
        {
            if (! string.IsNullOrEmpty(password))
            {
                await vm.updateAPI(password, oldPwd);
                password = string.Empty;
                oldPwd = string.Empty;
                oldPassword.Password = string.Empty;
                newPassword.Password = string.Empty;
                retypePassword.Password = string.Empty;
            }
            else
                await vm.updateAPI();
        }

        /// <summary>
        /// Handles the Click event of the Password control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
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

        /// <summary>
        /// Handles the Click event of the Img control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
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

            StorageFile file = await filePicker.PickSingleFileAsync();
            if (file != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
                dialog.ShowAsync();

                var stream = await file.OpenAsync(Windows.Storage.FileAccessMode.Read);
                var bitmapImage = new Windows.UI.Xaml.Media.Imaging.BitmapImage();
                await bitmapImage.SetSourceAsync(stream);

                var decoder = await Windows.Graphics.Imaging.BitmapDecoder.CreateAsync(stream);
                img.Source = bitmapImage;

                //For Convert Bitmap Image to Base64
                string newAvatar = await StorageFileToBase64(file);
                vm.avatar = newAvatar;

                dialog.Hide();
            }
        }

        /// <summary>
        /// Storages the file to base64.
        /// </summary>
        /// <param name="file">The file.</param>
        /// <returns></returns>
        private async Task<string> StorageFileToBase64(StorageFile file)
        {
            string Base64String = string.Empty;

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

        /// <summary>
        /// Affs the message.
        /// </summary>
        /// <param name="isError">if set to <c>true</c> [is error].</param>
        /// <param name="message">The message.</param>
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

        /// <summary>
        /// Handles the GotFocus event of the TextBox control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void TextBox_GotFocus(object sender, RoutedEventArgs e)
        {
            AppBar.Visibility = Visibility.Collapsed;
        }

        /// <summary>
        /// Handles the LostFocus event of the TextBox control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void TextBox_LostFocus(object sender, RoutedEventArgs e)
        {
            AppBar.Visibility = Visibility.Visible;
        }
    }
}