using GrappBox.ApiCom;
using GrappBox.Ressources;
using GrappBox.View;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Net.Http;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Xml.Linq;
using Windows.UI.Core;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Documents;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

namespace GrappBox.CustomControler.SlidingMenu
{
    [TemplatePart(Name = ElementLeftSideMenu, Type = typeof(FrameworkElement))]
    [TemplatePart(Name = ElementMenuBar, Type = typeof(Grid))]
    [TemplatePart(Name = ElementDisableContentOverlay, Type = typeof(Border))]
    [TemplatePart(Name = ElementMenuButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementDashboardButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementWhiteboardButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementUserSettingsButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementProjectSettingsButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementBugtrackerButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementTimelineButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementCloudButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementCalendarButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementProjectName, Type = typeof(TextBlock))]
    [TemplatePart(Name = ElementAvatar, Type = typeof(ImageBrush))]
    public sealed class SlideInMenuContentControl : ContentControl
    {
        enum MenuState
        {
            Menu = 0,
            Content
        };

        private List<StackPanel> buttons;

        Frame frame = Window.Current.Content as Frame;
        public static readonly DependencyProperty PageTitleProperty =
            DependencyProperty.Register("PageTitle", typeof(string), typeof(SlideInMenuContentControl), new PropertyMetadata(null));

        public static readonly DependencyProperty LeftSideMenuWidthProperty =
            DependencyProperty.Register("LeftSideMenuWidth", typeof(double), typeof(SlideInMenuContentControl), new PropertyMetadata(250.0));

        private const string ElementLeftSideMenu = "ContentLeftSideMenu";
        private const string ElementMenuBar = "Menubar";
        private const string ElementDisableContentOverlay = "DisableContentOverlay";

        private const string ElementMenuButton = "MenuButton";

        private const string ElementAvatar = "Avatar";

        private const string ElementDashboardButton = "DashboardButton";
        private const string ElementWhiteboardButton = "WhiteboardButton";
        private const string ElementUserSettingsButton = "UserSettingsButton";
        private const string ElementProjectSettingsButton = "ProjectSettingsButton";
        private const string ElementBugtrackerButton = "BugtrackerButton";
        private const string ElementTimelineButton = "TimelineButton";
        private const string ElementCloudButton = "CloudButton";
        private const string ElementCalendarButton = "CalendarButton";
        private const string ElementLogoutButton = "LogoutButton";
        private const string ElementProjectName = "ProjectName";

        private const string ElementCloudPanel = "CloudPanel";
        private const string ElementTimelinePanel = "TimelinePanel";
        private const string ElementBugtrackerPanel = "BugtrackerPanel";
        private const string ElementWhiteboardPanel = "WhiteboardPanel";
        private const string ElementProjectSettingsPanel = "ProjectSettingsPanel";

        private FrameworkElement leftSideMenu;
        private StackPanel menuBar;
        private Border disableContentOverlay;
        private Button menuButton;
        private Button dashboardButton;
        private Button whiteboardButton;
        private Button userSettingsButton;
        private Button projectSettingsButton;
        private Button bugtrackerButton;
        private Button timelineButton;
        private Button cloudButton;
        private Button calendarButton;
        private Button logoutButton;
        private TextBlock projectName;
        private ImageBrush avatar;

        public SlideInMenuContentControl()
        {
            this.DefaultStyleKey = typeof(SlideInMenuContentControl);
        }

        public string PageTitle
        {
            get { return (string)GetValue(PageTitleProperty); }
            set { SetValue(PageTitleProperty, value); }
        }

        public double LeftSideMenuWidth
        {
            get { return (double)GetValue(LeftSideMenuWidthProperty); }
            set { SetValue(LeftSideMenuWidthProperty, value); }
        }

        protected override void OnApplyTemplate()
        {
            base.OnApplyTemplate();

            menuBar = GetTemplateChild(ElementMenuBar) as StackPanel;
            leftSideMenu = GetTemplateChild(ElementLeftSideMenu) as FrameworkElement;
            disableContentOverlay = GetTemplateChild(ElementDisableContentOverlay) as Border;
            menuButton = GetTemplateChild(ElementMenuButton) as Button;
            dashboardButton = GetTemplateChild(ElementDashboardButton) as Button;
            whiteboardButton = GetTemplateChild(ElementWhiteboardButton) as Button;
            userSettingsButton = GetTemplateChild(ElementUserSettingsButton) as Button;
            projectSettingsButton = GetTemplateChild(ElementProjectSettingsButton) as Button;
            bugtrackerButton = GetTemplateChild(ElementBugtrackerButton) as Button;
            timelineButton = GetTemplateChild(ElementTimelineButton) as Button;
            cloudButton = GetTemplateChild(ElementCloudButton) as Button;
            calendarButton = GetTemplateChild(ElementCalendarButton) as Button;
            logoutButton = GetTemplateChild(ElementLogoutButton) as Button;
            projectName = GetTemplateChild(ElementProjectName) as TextBlock;
            avatar = GetTemplateChild(ElementAvatar) as ImageBrush;


            var contentFrame = GetTemplateChild("ContentFrame") as FrameworkElement;
            contentFrame.Width = Window.Current.Bounds.Width;

            projectName.Text = SettingsManager.getOption<string>("ProjectNameChoosen") ?? "";
            avatar.ImageSource = User.GetUser().Img;

            buttons = new List<StackPanel>();
            buttons.Add(GetTemplateChild(ElementBugtrackerPanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementCloudPanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementTimelinePanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementProjectSettingsPanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementWhiteboardPanel) as StackPanel);
            
            menuBar.ManipulationDelta += ContentSelector_ManipulationDelta;
            menuBar.ManipulationCompleted += ContentSelector_ManipulationCompleted;

            menuButton.Click += MenuButton_Click; ;
            dashboardButton.Tapped += DashboardButton_Tapped;
            whiteboardButton.Tapped += WhiteboardButton_Tapped;
            userSettingsButton.Tapped += UserSettingsButton_Tapped;
            projectSettingsButton.Tapped += ProjectSettingsButton_Tapped;
            bugtrackerButton.Tapped += BugtrackerButton_Tapped;
            timelineButton.Tapped += TimelineButton_Tapped;
            cloudButton.Tapped += CloudButton_Tapped;
            calendarButton.Tapped += CalendarButton_Tapped;
            logoutButton.Tapped += LogoutButton_Tapped;
            UpdateMenu();
        }

        private MenuState menuState = MenuState.Content;

        private void ContentSelector_ManipulationCompleted(object sender, ManipulationCompletedRoutedEventArgs e)
        {
            if (menuState == MenuState.Content)
                if (leftSideMenu.ActualWidth > LeftSideMenuWidth / 2)
                {
                    leftSideMenu.Width = LeftSideMenuWidth;
                    menuState = MenuState.Menu;
                }
                else
                {
                    leftSideMenu.Width = 0;
                    menuState = MenuState.Content;
                }
            else
            {
                if (leftSideMenu.ActualWidth < LeftSideMenuWidth / 2)
                {
                    leftSideMenu.Width = 0;
                    menuState = MenuState.Content;
                }
                else
                {
                    leftSideMenu.Width = LeftSideMenuWidth;
                    menuState = MenuState.Menu;
                }
            }
        }

        private void ContentSelector_ManipulationDelta(object sender, ManipulationDeltaRoutedEventArgs e)
        {
            if (menuState == MenuState.Content)
            {
                if (leftSideMenu.ActualWidth < LeftSideMenuWidth)
                    leftSideMenu.Width += (leftSideMenu.Width + e.Delta.Translation.X) < 0 ? 0 : e.Delta.Translation.X;
            }
            else
            {
                if (leftSideMenu.ActualWidth > 0)
                    leftSideMenu.Width += (leftSideMenu.Width + e.Delta.Translation.X) < 0 ? 0 : e.Delta.Translation.X;
            }
        }

        private void MenuButton_Click(object sender, RoutedEventArgs e)
        {
            if (menuState == MenuState.Menu)
                DisplayContent();
            else
                DisplayMenu();
        }

        void UpdateMenu()
        {
            int id = SettingsManager.getOption<int>("ProjectIdChoosen");
            foreach (StackPanel b in buttons)
                b.Visibility = id == 0 ? Visibility.Collapsed : Visibility.Visible;
        }

        private async void DashboardButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(GenericDahsboard)));
        }

        private async void WhiteboardButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(WhiteBoardListView)));
        }

        private async void UserSettingsButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(UserView)));
        }

        private async void ProjectSettingsButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(ProjectSettingsView)));
        }

        private async void BugtrackerButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(BugtrackerView)));
        }

        private async void TimelineButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(TimelineView)));
        }

        private async void CloudButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(CloudView)));
        }

        private async void CalendarButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                frame.Navigate(typeof(Calendar)));
        }

        private void LogoutButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            logout();
        }

        public void DisplayMenu()
        {
            UpdateMenu();
            leftSideMenu.Width = LeftSideMenuWidth;
            menuState = MenuState.Menu;
        }

        public void DisplayContent()
        {
            leftSideMenu.Width = 0;
            menuState = MenuState.Content;
        }

        private async void logout()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "accountadministration/logout");
            if (res.IsSuccessStatusCode)
            {
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                    frame.Navigate(typeof(MainPage)));
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
    }
}