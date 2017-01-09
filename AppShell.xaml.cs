using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.View;
using System;
using System.Collections.ObjectModel;
using System.Linq;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Core;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Automation;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.Web.Http;

namespace Grappbox
{
    /// <summary>
    /// The "chrome" layer of the app that provides top-level navigation with
    /// proper keyboarding navigation.
    /// </summary>
    public sealed partial class AppShell : Page
    {
        private SessionHelper session = null;
        private static AppShell instance;
        private bool _isPaddingAdded = false;

        // Declare the top level nav items
        private ObservableCollection<NavMenuItem> partialNavList = new ObservableCollection<NavMenuItem>(
            new[]
            {
                new NavMenuItem()
                {
                    Symbol = Constants.DashboardSymbol,
                    Label = "Dashboard",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.CalendarSymbol,
                    Label = "Calendar",
                    DestPage = typeof(View.CalendarView),
                    ForegroundColor = SystemInformation.GetStaticResource("BlueGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.CloudSymbol,
                    Label = "Cloud",
                    DestPage = typeof(View.CloudView),
                    ForegroundColor = SystemInformation.GetStaticResource("YellowGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.TimelineSymbol,
                    Label = "Talks",
                    DestPage = typeof(TimelineView),
                    ForegroundColor = SystemInformation.GetStaticResource("OrangeGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.BugtrackerSymbol,
                    Label = "Bugtracker",
                    DestPage = typeof(BugtrackerView),
                    ForegroundColor = SystemInformation.GetStaticResource("PurpleGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.TasksSymbol,
                    Label = "Tasks",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("BlueGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.GanttSymbol,
                    Label = "Gantt",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("BlueGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.WhiteboardSymbol,
                    Label = "Whiteboard",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("GreenGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.StatsSymbol,
                    Label = "Statistics",
                    DestPage = typeof(StatsView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.ProjectSettingsSymbol,
                    Label = "Project Settings",
                    DestPage = typeof(ProjectSettingsView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.UserSettingsSymbol,
                    Label = "User Settings",
                    DestPage = typeof(UserView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.LogoutSymbol,
                    Label = "Logout",
                    DestPage = typeof(LoginPage),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
            });

        private ObservableCollection<NavMenuItem> completeNavList = new ObservableCollection<NavMenuItem>() {
            new NavMenuItem()
                {
                    Symbol = Constants.DashboardSymbol,
                    Label = "Dashboard",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.CalendarSymbol,
                    Label = "Calendar",
                    DestPage = typeof(View.CalendarView),
                    ForegroundColor = SystemInformation.GetStaticResource("BlueGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.CloudSymbol,
                    Label = "Cloud",
                    DestPage = typeof(View.CloudView),
                    ForegroundColor = SystemInformation.GetStaticResource("YellowGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.TimelineSymbol,
                    Label = "Talks",
                    DestPage = typeof(TimelineView),
                    ForegroundColor = SystemInformation.GetStaticResource("OrangeGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.BugtrackerSymbol,
                    Label = "Bugtracker",
                    DestPage = typeof(BugtrackerView),
                    ForegroundColor = SystemInformation.GetStaticResource("PurpleGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.TasksSymbol,
                    Label = "Tasks",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("BlueGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.GanttSymbol,
                    Label = "Gantt",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("BlueGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.WhiteboardSymbol,
                    Label = "Whiteboard",
                    DestPage = typeof(DashBoardView),
                    ForegroundColor = SystemInformation.GetStaticResource("GreenGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.StatsSymbol,
                    Label = "Statistics",
                    DestPage = typeof(StatsView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.ProjectSettingsSymbol,
                    Label = "Project Settings",
                    DestPage = typeof(ProjectSettingsView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.UserSettingsSymbol,
                    Label = "User Settings",
                    DestPage = typeof(UserView),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
                new NavMenuItem()
                {
                    Symbol = Constants.LogoutSymbol,
                    Label = "Logout",
                    DestPage = typeof(LoginPage),
                    ForegroundColor = SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush
                },
        };

        public static AppShell Current = null;

        /// <summary>
        /// Initializes a new instance of the AppShell, sets the static 'Current' reference,
        /// adds callbacks for Back requests and changes in the SplitView's DisplayMode, and
        /// provide the nav menu list with the data to display.
        /// </summary>
        public AppShell()
        {
            this.InitializeComponent();

            this.Loaded += (sender, args) =>
            {
                Current = this;

                this.CheckTogglePaneButtonSizeChanged();
            };

            this.RootSplitView.RegisterPropertyChangedCallback(
                SplitView.DisplayModeProperty,
                (s, a) =>
                {
                    // Ensure that we update the reported size of the TogglePaneButton when the SplitView's
                    // DisplayMode changes.
                    this.CheckTogglePaneButtonSizeChanged();
                });

            SystemNavigationManager.GetForCurrentView().BackRequested += SystemNavigationManager_BackRequested;
            SystemNavigationManager.GetForCurrentView().AppViewBackButtonVisibility = AppViewBackButtonVisibility.Visible;

            instance = null;
            NavMenuList.ItemsSource = NavList;
        }

        public ObservableCollection<NavMenuItem> NavList
        {
            get
            {
                if (session != null && session.IsProjectSelected == true)
                    return completeNavList;
                else
                    return partialNavList;
            }
        }

        public Frame AppFrame { get { return this.frame; } }

        /// <summary>
        /// Invoked when window title bar visibility changes, such as after loading or in tablet mode
        /// Ensures correct padding at window top, between title bar and app content
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="args"></param>
        private void TitleBar_IsVisibleChanged(Windows.ApplicationModel.Core.CoreApplicationViewTitleBar sender, object args)
        {
            if (!this._isPaddingAdded && sender.IsVisible)
            {
                //add extra padding between window title bar and app content
                double extraPadding = (Double)App.Current.Resources["DesktopWindowTopPadding"];
                this._isPaddingAdded = true;

                Thickness margin = NavMenuList.Margin;
                NavMenuList.Margin = new Thickness(margin.Left, margin.Top + extraPadding, margin.Right, margin.Bottom);
                margin = AppFrame.Margin;
                AppFrame.Margin = new Thickness(margin.Left, margin.Top + extraPadding, margin.Right, margin.Bottom);
                margin = TogglePaneButton.Margin;
                TogglePaneButton.Margin = new Thickness(margin.Left, margin.Top + extraPadding, margin.Right, margin.Bottom);
            }
        }

        #region BackRequested Handlers

        private void SystemNavigationManager_BackRequested(object sender, BackRequestedEventArgs e)
        {
            bool handled = e.Handled;
            this.BackRequested(ref handled);
            e.Handled = handled;
        }

        private void BackRequested(ref bool handled)
        {
            // Get a hold of the current frame so that we can inspect the app back stack.

            if (this.AppFrame == null)
                return;

            // Check to see if this is the top-most page on the app back stack.
            if (this.AppFrame.CanGoBack && !handled)
            {
                // If not, set the event to handled and go back to the previous page in the app.
                handled = true;
                this.AppFrame.GoBack();
            }
        }

        #endregion BackRequested Handlers

        #region Navigation

        /// <summary>
        /// Navigate to the Page for the selected <paramref name="listViewItem"/>.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="listViewItem"></param>
        private async void NavMenuList_ItemInvoked(object sender, ListViewItem listViewItem)
        {
            foreach (var i in NavList)
            {
                i.IsSelected = false;
            }

            var item = (NavMenuItem)((NavMenuListView)sender).ItemFromContainer(listViewItem);

            if (item != null)
            {
                item.IsSelected = true;
                if (item.DestPage != null &&
                    item.DestPage != this.AppFrame.CurrentSourcePageType)
                {
                    if (item.Label == "Logout")
                        await logout();
                    this.AppFrame.Navigate(item.DestPage, item.Arguments);
                }
            }
        }

        private async System.Threading.Tasks.Task logout()
        {
            HttpResponseMessage res = await HttpRequestManager.Get(null, "account/logout");
            if (res.IsSuccessStatusCode)
            {
                SessionHelper.GetSession().ResetSession();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        /// <summary>
        /// Ensures the nav menu reflects reality when navigation is triggered outside of
        /// the nav menu buttons.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void OnNavigatingToPage(object sender, NavigatingCancelEventArgs e)
        {
            if (e.SourcePageType == typeof(LoginPage))
                TogglePaneButton.Visibility = Visibility.Collapsed;
            else
            {
                TogglePaneButton.Visibility = Visibility.Visible;
                this.CheckTogglePaneButtonSizeChanged();
            }
            if (e.NavigationMode != NavigationMode.Back) return;
            var item = (from p in this.NavList where p.DestPage == e.SourcePageType select p).FirstOrDefault();
            if (item == null && this.AppFrame.BackStackDepth > 0)
            {
                // In cases where a page drills into sub-pages then we'll highlight the most recent
                // navigation menu item that appears in the BackStack
                foreach (var entry in this.AppFrame.BackStack.Reverse())
                {
                    item = (from p in this.NavList where p.DestPage == entry.SourcePageType select p).SingleOrDefault();
                    if (item != null)
                        break;
                }
            }

            foreach (var i in NavList)
            {
                i.IsSelected = false;
            }
            if (item != null)
            {
                item.IsSelected = true;
            }

            var container = (ListViewItem)NavMenuList.ContainerFromItem(item);
            NavMenuList.SetSelectedItem(container);
        }

        #endregion Navigation

        public Rect TogglePaneButtonRect
        {
            get;
            private set;
        }

        /// <summary>
        /// An event to notify listeners when the hamburger button may occlude other content in the app.
        /// The custom "PageHeader" user control is using this.
        /// </summary>
        public event TypedEventHandler<AppShell, Rect> TogglePaneButtonRectChanged;

        /// <summary>
        /// Public method to allow pages to open SplitView's pane.
        /// Used for custom app shortcuts like navigating left from page's left-most item
        /// </summary>
        public void OpenNavePane()
        {
            TogglePaneButton.IsChecked = true;
        }

        /// <summary>
        /// Callback when the SplitView's Pane is toggled closed.  When the Pane is not visible
        /// then the floating hamburger may be occluding other content in the app unless it is aware.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void TogglePaneButton_Unchecked(object sender, RoutedEventArgs e)
        {
            this.TogglePaneButton.Foreground = new SolidColorBrush(Colors.White);
            this.CheckTogglePaneButtonSizeChanged();
        }

        /// <summary>
        /// Callback when the SplitView's Pane is toggled opened.
        /// Restores divider's visibility and ensures that margins around the floating hamburger are correctly set.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        private void TogglePaneButton_Checked(object sender, RoutedEventArgs e)
        {
            CheckUserIdentity();
            this.TogglePaneButton.Foreground = new SolidColorBrush(Colors.Black);
            this.CheckTogglePaneButtonSizeChanged();
        }

        /// <summary>
        /// Check for the conditions where the navigation pane does not occupy the space under the floating
        /// hamburger button and trigger the event.
        /// </summary>
        private void CheckTogglePaneButtonSizeChanged()
        {
            if (this.RootSplitView.DisplayMode == SplitViewDisplayMode.Inline ||
                this.RootSplitView.DisplayMode == SplitViewDisplayMode.Overlay)
            {
                var transform = this.TogglePaneButton.TransformToVisual(this);
                var rect = transform.TransformBounds(new Rect(0, 0, this.TogglePaneButton.ActualWidth, this.TogglePaneButton.ActualHeight));
                this.TogglePaneButtonRect = rect;
            }
            else
            {
                this.TogglePaneButtonRect = new Rect();
            }

            var handler = this.TogglePaneButtonRectChanged;
            if (handler != null)
            {
                // handler(this, this.TogglePaneButtonRect);
                handler.DynamicInvoke(this, this.TogglePaneButtonRect);
            }
        }

        /// <summary>
        /// Enable accessibility on each nav menu item by setting the AutomationProperties.Name on each container
        /// using the associated Label of each item.
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="args"></param>
        private void NavMenuItemContainerContentChanging(ListViewBase sender, ContainerContentChangingEventArgs args)
        {
            if (!args.InRecycleQueue && args.Item != null && args.Item is NavMenuItem)
            {
                args.ItemContainer.SetValue(AutomationProperties.NameProperty, ((NavMenuItem)args.Item).Label);
            }
            else
            {
                args.ItemContainer.ClearValue(AutomationProperties.NameProperty);
            }
        }

        private void Button_Click(object sender, RoutedEventArgs e)
        {
            this.AppFrame.Navigate(typeof(GenericDahsboard));
        }

        private void CheckUserIdentity()
        {
            if (session == null)
                return;
            if (session.IsUserConnected == true)
            {
                UserNameTextBlock.Text = session.UserName;
            }
        }
    }
}