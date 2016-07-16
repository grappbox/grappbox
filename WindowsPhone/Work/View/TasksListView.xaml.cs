using GrappBox.Model.Tasks;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.ApplicationModel.Core;
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

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class TasksListView : Page
    {
        private NavigationHelper navigationHelper;
        CoreApplicationView view;
        TasksViewModel vm = TasksViewModel.GetViewModel();
        public TasksListView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Required;

            this.navigationHelper = new NavigationHelper(this);
        }

        #region NavigationHelper

        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            this.navigationHelper.OnNavigatedTo(e);
            vm.TaskSelect = null;
            await vm.getTasksList();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private async void Add_Click(object sender, RoutedEventArgs e)
        {
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(TasksView), null));
        }

        private void taskListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListBox).SelectedItem != null)
                vm.TaskSelect = (sender as ListBox).SelectedItem as TaskModel;
        }

        private async void Remove_Click(object sender, RoutedEventArgs e)
        {
            if (vm.TaskSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.deleteTask();

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void EditTask_Click(object sender, RoutedEventArgs e)
        {
            vm.setModel(vm.TaskSelect);
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(TasksView), vm.TaskSelect.Id));
        }
    }
}
