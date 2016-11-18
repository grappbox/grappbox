using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
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
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            dialog.ShowAsync();
            ViewModel.IsBusy = true;
            await ViewModel.PickDay(DateTime.Today);
            dialog.Hide();
            ViewModel.IsBusy = false;
        }

        private async void CalendarView_SelectedDatesChanged(Windows.UI.Xaml.Controls.CalendarView sender, CalendarViewSelectedDatesChangedEventArgs args)
        {
            if (this.ViewModel.IsBusy)
                return;
            ViewModel.IsBusy = true;
            DateTimeOffset selectedDate = args.AddedDates[0];
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            dialog.ShowAsync();
            await this.ViewModel.PickDay(selectedDate.Date);
            dialog.Hide();
            ViewModel.IsBusy = false;
        }
    }
}