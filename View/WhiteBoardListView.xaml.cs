using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.CustomControls.Whiteboard;
using Grappbox.CustomControls;
using Grappbox.Helpers;
using Windows.UI.ViewManagement;
using Windows.UI;
using Windows.UI.Popups;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class WhiteBoardListView : Page
    {
        public WhiteBoardListView()
        {
            this.InitializeComponent();
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            StatusBar.GetForCurrentView().BackgroundColor = (Color)SystemInformation.GetStaticResource("GreenGrappbox");
            LoaderDialog loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("GreenGrappboxBrush"));
            loader.ShowAsync();
            await viewModel.GetWhiteboards();
            loader.Hide();
        }

        private async void CreateWhiteBoard(object sender, RoutedEventArgs e)
        {
            NewWhiteboard dialog = new NewWhiteboard();
            await dialog.ShowAsync();
            if (dialog.Result == ContentDialogResult.Primary)
            {
                LoaderDialog loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("GreenGrappboxBrush"));
                loader.ShowAsync();
                await viewModel.CreateWhiteboard(dialog.WhiteBoardName);
                loader.Hide();
            }
        }

        private void ListView_ItemClick(object sender, ItemClickEventArgs e)
        {
            WhiteBoardListModel wblm = e.ClickedItem as WhiteBoardListModel;
            Debug.WriteLine(wblm.Name);
            this.Frame.Navigate(typeof(WhiteBoardView), wblm.Id);
        }

        private async void MenuFlyoutItem_Click(object sender, RoutedEventArgs e)
        {
            var confirmDialog = new ConfirmDeleteDialog("Delete Whiteboard", "Are you sure ?", SystemInformation.GetStaticResource<SolidColorBrush>("GreenGrappboxBrush"));
            await confirmDialog.ShowAsync();
            if (!confirmDialog.ConfirmDelete)
                return;
            var dialog = new Grappbox.CustomControls.LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("GreenGrappboxBrush"));
            dialog.ShowAsync().GetResults();
            bool result = await viewModel.DeleteWhiteboard(viewModel.ToDelete.Id);
            if (!result)
            {
                dialog.Hide();
                var errorDialog = new MessageDialog("Can't delete the whiteboard");
                await errorDialog.ShowAsync();
                return;
            }
            await viewModel.GetWhiteboards();
            dialog.Hide();
        }

        private void Grid_Holding(object sender, HoldingRoutedEventArgs e)
        {
            FrameworkElement senderElement = sender as FrameworkElement;
            viewModel.ToDelete = senderElement.DataContext as WhiteBoardListModel;
            FlyoutBase flyoutBase = FlyoutBase.GetAttachedFlyout(senderElement);
            flyoutBase.ShowAt(senderElement);
        }
    }
}
