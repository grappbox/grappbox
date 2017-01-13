using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.Foundation.Metadata;
using Windows.UI;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.Model;
using Grappbox.View;
using Windows.UI.Popups;

// Pour plus d'informations sur le modèle d'élément Page vierge, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class CalendarView : Page
    {
        public CalendarViewModel ViewModel { get; private set; }

        public CalendarView()
        {
            this.InitializeComponent();
            ViewModel = new CalendarViewModel();
            this.DataContext = ViewModel;
        }

        protected override async void OnNavigatedTo(NavigationEventArgs e)
        {
            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["BlueGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            base.OnNavigatedTo(e);
            var dialog = new Grappbox.CustomControls.LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            dialog.ShowAsync().GetResults();
            ViewModel.IsBusy = true;
            await ViewModel.PickDay(DateTime.Today);
            dialog.Hide();
            ViewModel.IsBusy = false;
        }

        private async void CalendarView_SelectedDatesChanged(Windows.UI.Xaml.Controls.CalendarView sender, CalendarViewSelectedDatesChangedEventArgs args)
        {
            if (this.ViewModel.IsBusy)
                return;
            var dates = new List<DateTimeOffset>(args.AddedDates);
            if (dates.Count < 1)
                return;
            ViewModel.IsBusy = true;
            var selectedDate = dates[0];
            var dialog = new Grappbox.CustomControls.LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            dialog.ShowAsync().GetResults();
            await this.ViewModel.PickDay(selectedDate.Date);
            dialog.Hide();
            ViewModel.IsBusy = false;
        }

        private void ListViewBase_OnItemClick(object sender, ItemClickEventArgs e)
        {
            this.Frame.Navigate(typeof(CalendarEventDetail), e.ClickedItem as EventViewModel);
        }

        private void Button_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(CalendarEventAdd), Calendar.SelectedDates.Count > 0 ? Calendar.SelectedDates[0] : DateTimeOffset.Now );
        }

        private async void MenuFlyoutItem_Click(object sender, RoutedEventArgs e)
        {
            var confirmDialog = new ConfirmDeleteDialog("Delete event", "Are you sure ?", SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            await confirmDialog.ShowAsync();
            if (!confirmDialog.ConfirmDelete)
                return;
            var dialog = new Grappbox.CustomControls.LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            dialog.ShowAsync().GetResults();
            ViewModel.IsBusy = true;
            bool result = await ViewModel.DeleteEvent(ViewModel.ToDelete.Id);
            if (!result)
            {
                dialog.Hide();
                var errorDialog = new MessageDialog("Can't delete the event");
                await errorDialog.ShowAsync();
                return;
            }
            await ViewModel.ForceReset(ViewModel.CurrentDate);
            dialog.Hide();
            ViewModel.IsBusy = false;
        }

        private void Grid_Holding(object sender, HoldingRoutedEventArgs e)
        {
            FrameworkElement senderElement = sender as FrameworkElement;
            ViewModel.ToDelete = senderElement.DataContext as EventViewModel;
            FlyoutBase flyoutBase = FlyoutBase.GetAttachedFlyout(senderElement);
            flyoutBase.ShowAt(senderElement);
        }
    }
}