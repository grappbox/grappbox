using GrappBox.Resources;
using GrappBox.ViewModel;
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

// Pour en savoir plus sur le modèle d’élément Page vierge, consultez la page http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class TasksView : Page
    {
        TasksViewModel vm = TasksViewModel.GetViewModel();
        bool isAdd;

        //Required for navigation
        private readonly NavigationHelper navigationHelper;
        public TasksView()
        {
            this.InitializeComponent();
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
            if (e.Parameter != null)
                isAdd = false;
            else
            {
                isAdd = true;

                vm.Model = new Model.Tasks.TaskModel();
                Title.Text = "";
                Description.Text = "";

                Title.IsEnabled = true;
                Description.IsEnabled = true;
            }
        }

        public DateTime BeginDate
        {
            get { return new DateTime(beginDate.Date.Year, beginDate.Date.Month, beginDate.Date.Day, beginHour.Time.Hours, beginHour.Time.Minutes, 0); }
            set { beginDate.Date = Convert.ToDateTime(vm.BeginDate.date); beginHour.Time = DateTime.Now.Subtract(Convert.ToDateTime(vm.BeginDate.date)); }
        }
        
        public DateTime EndDate
        {
            get { return new DateTime(endDate.Date.Year, endDate.Date.Month, endDate.Date.Day, endHour.Time.Hours, endHour.Time.Minutes, 0); }
            set { endDate.Date = Convert.ToDateTime(vm.DueDate.date); endHour.Time = DateTime.Now.Subtract(Convert.ToDateTime(vm.DueDate.date)); }
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private async void SaveTask_Click(object sender, RoutedEventArgs e)
        {
            if (vm.Title != "" && vm.Description != "")
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;
                vm.BeginDate.date = BeginDate.ToString("yyyy-MM-dd HH:mm:ss");
                vm.DueDate.date = BeginDate.ToString("yyyy-MM-dd HH:mm:ss");

                if (isAdd == true)
                {
                    await vm.addTask();
                }
                else
                {
                    await vm.editTask();
                }

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }
    }
}
