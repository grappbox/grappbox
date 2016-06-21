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
    [TemplatePart(Name = ElementContentSelector, Type = typeof(Selector))]
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
    public sealed class SlideInMenuContentControl : ContentControl
    {
        private List<StackPanel> buttons;

        Frame frame = Window.Current.Content as Frame;
        public static readonly DependencyProperty PageTitleProperty =
            DependencyProperty.Register("PageTitle", typeof(string), typeof(SlideInMenuContentControl), new PropertyMetadata(null));

        public static readonly DependencyProperty LeftSideMenuWidthProperty =
            DependencyProperty.Register("LeftSideMenuWidth", typeof(double), typeof(SlideInMenuContentControl), new PropertyMetadata(250.0));

        private const string ElementLeftSideMenu = "ContentLeftSideMenu";
        private const string ElementContentSelector = "ContentSelector";
        private const string ElementDisableContentOverlay = "DisableContentOverlay";

        private const string ElementMenuButton = "MenuButton";

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
        private Selector contentSelector;
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

            contentSelector = GetTemplateChild(ElementContentSelector) as Selector;
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

            var contentFrame = GetTemplateChild("ContentFrame") as FrameworkElement;
            contentFrame.Width = Window.Current.Bounds.Width;

            projectName.Text = SettingsManager.getOption<string>("ProjectNameChoosen") ?? "";

            buttons = new List<StackPanel>();
            buttons.Add(GetTemplateChild(ElementBugtrackerPanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementCloudPanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementTimelinePanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementProjectSettingsPanel) as StackPanel);
            buttons.Add(GetTemplateChild(ElementWhiteboardPanel) as StackPanel);

            contentSelector.SelectionChanged += ContentSelector_SelectionChanged;
            SetMenuVisibility();
            menuButton.Tapped += MenuButton_Tapped;
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

        void UpdateMenu()
        {
            int id = SettingsManager.getOption<int>("ProjectIdChoosen");
            foreach (StackPanel b in buttons)
                b.Visibility = id == 0 ? Visibility.Collapsed : Visibility.Visible;
        }

        private void DashboardButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(GenericDahsboard));
        }

        private void WhiteboardButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(WhiteBoardListView));
        }

        private void UserSettingsButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(UserView));
        }

        private void ProjectSettingsButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(ProjectSettingsView));
        }

        private void BugtrackerButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(BugtrackerView));
        }

        private void TimelineButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(TimelineView));
        }

        private void CloudButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            DisplayContent();
            frame.Navigate(typeof(CloudView));
        }

        private void CalendarButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            frame.Navigate(typeof(Calendar));
        }

        private void LogoutButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            logout();
        }

        private void MenuButton_Tapped(object sender, TappedRoutedEventArgs e)
        {
            Debug.WriteLine("MenuButton_Start_{0}", contentSelector.SelectedIndex);
            if (contentSelector.SelectedIndex == 0)
                DisplayContent();             
            else                              
                DisplayMenu();                
            Debug.WriteLine("MenuButton_End___{0}", contentSelector.SelectedIndex);
        }

        public void DisplayMenu()
        {
            UpdateMenu();
            contentSelector.SelectedIndex = -1;
            contentSelector.SelectedIndex = 0;
        }

        public void DisplayContent()
        {
            contentSelector.SelectedIndex = 0;
            contentSelector.SelectedIndex = 1;
        }

        private void SetMenuVisibility()
        {
            if (leftSideMenu != null && contentSelector != null)
            {
                leftSideMenu.Visibility = Visibility.Visible;
                contentSelector.SelectedIndex = 1;
            }
        }

        private async void logout()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "accountadministration/logout");
            if (res.IsSuccessStatusCode)
            {
                frame.Navigate(typeof(MainPage));
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        private void ContentSelector_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Debug.WriteLine(contentSelector.SelectedIndex);/*
            if (contentSelector.SelectedIndex == 0)
            {
                disableContentOverlay.Visibility = Visibility.Visible;
            }
            else
            {
                disableContentOverlay.Visibility = Visibility.Collapsed;
            }*/
        }
    }
}