using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Threading.Tasks;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.Core;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.Storage.Streams;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Media.Imaging;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class CloudView : Page
    {
        TimelineViewModel tvm = new TimelineViewModel();
        CoreApplicationView view;
        CloudViewModel vm = CloudViewModel.GetViewModel();

        //Required for navigation
        private readonly NavigationHelper navigationHelper;
        public CloudView()
        {
            this.InitializeComponent();
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

            this.navigationHelper.OnNavigatedTo(e);
            await vm.getLS();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private async void listView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.FileSelect = (sender as ListBox).SelectedItem as CloudModel;
            if (vm.FileSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                vm.FullPath.Add(vm.FileSelect.Filename);
                if (vm.FileSelect.Type == "dir")
                {
                    LoadingBar.IsEnabled = true;
                    LoadingBar.Visibility = Visibility.Visible;

                    await vm.getLS();
                    vm.FileSelect = null;

                    LoadingBar.IsEnabled = false;
                    LoadingBar.Visibility = Visibility.Collapsed;
                }
                else
                    (sender as ListBox).SelectedItem = null;
            }
        }

        private void Grid_Loaded(object sender, RoutedEventArgs e)
        {
            Grid grid = sender as Grid;
            if (((sender as Grid).DataContext as CloudModel).Type == "file")
                ((Image)grid.Children[0]).Source = new BitmapImage(new Uri("ms-appx:///Assets/file.png", UriKind.Absolute));
            else if (((sender as Grid).DataContext as CloudModel).Type == "dir")
                ((Image)grid.Children[0]).Source = new BitmapImage(new Uri("ms-appx:///Assets/folder.png", UriKind.Absolute));
        }

        private void AddFolder_Click(object sender, RoutedEventArgs e)
        {
            AddFolderPopUp.Visibility = Visibility.Visible;
            FileListView.IsEnabled = false;
            CB.IsEnabled = false;
            foreach (var item in vm.FullPath)
            {
                if (item == "Safe")
                {
                    FolderSafe.Visibility = Visibility.Visible;
                    FolderSafePassword.Visibility = Visibility.Visible;
                }
            }
        }

        private void UploadFile_Click(object sender, RoutedEventArgs e)
        {
            FileOpenPicker openPicker = new FileOpenPicker();
            openPicker.FileTypeFilter.Add("*");

            view.Activated += viewActivated;

            openPicker.PickSingleFileAndContinue();
        }

        private async void viewActivated(CoreApplicationView sender, IActivatedEventArgs args1)
        {
            FileOpenPickerContinuationEventArgs args = args1 as FileOpenPickerContinuationEventArgs;

            if (args != null)
            {
                if (args.Files.Count == 0) return;

                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                view.Activated -= viewActivated;
                vm.FileData = await StorageFileToBase64(args.Files[0]);
                await vm.uploadFile(args.Files[0].Name);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
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

        private async void CreateFolder_Click(object sender, RoutedEventArgs e)
        {
            if (vm.FolderName != null && vm.FolderName != "")
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.createDir();
                vm.FolderName = "";

                AddFolderPopUp.Visibility = Visibility.Collapsed;
                FolderSafe.Visibility = Visibility.Collapsed;
                FolderSafePassword.Visibility = Visibility.Collapsed;
                FileListView.IsEnabled = true;
                CB.IsEnabled = true;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void MenuFlyoutItem_DownloadClick(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            vm.FullPath.Add(vm.FileSelect.Filename);
            await vm.downloadFile();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void MenuFlyoutItem_DeleteClick(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            vm.FullPath.Add(vm.FileSelect.Filename);
            await vm.deleteFile();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void PreviousFolder_Click(object sender, RoutedEventArgs e)
        {
            if (vm.FullPath.Count > 0)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                vm.FullPath.RemoveAt(vm.FullPath.Count - 1);
                await vm.getLS();

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void Home_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            vm.FullPath.Clear();
            await vm.getLS();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private void CancelCreateFolder_Click(object sender, RoutedEventArgs e)
        {
            AddFolderPopUp.Visibility = Visibility.Collapsed;
            FolderSafe.Visibility = Visibility.Collapsed;
            FolderSafePassword.Visibility = Visibility.Collapsed;
            FileListView.IsEnabled = true;
            CB.IsEnabled = true;
        }

        private void Grid_Holding(object sender, HoldingRoutedEventArgs e)
        {
            FrameworkElement senderElement = sender as FrameworkElement;

            FlyoutBase flyoutBase = FlyoutBase.GetAttachedFlyout(senderElement);
            flyoutBase.ShowAt(senderElement);
            vm.FileSelect = (sender as Grid).DataContext as CloudModel;
        }
    }
}
