using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Documents;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

namespace GrappBox.CustomControler.SlidingMenu
{
    //[TemplatePart(Name = ElementLeftSideMenu, Type = typeof(FrameworkElement))]
    //[TemplatePart(Name = ElementRightSideMenu, Type = typeof(FrameworkElement))]
    //[TemplatePart(Name = ElementContentSelector, Type = typeof(Selector))]
    //[TemplatePart(Name = ElementDisableContentOverlay, Type = typeof(Border))]
    public sealed class SlideInMenuContentControl : ContentControl
    {
        public static readonly DependencyProperty LeftMenuContentProperty =
            DependencyProperty.Register("LeftMenuContent", typeof(object), typeof(SlideInMenuContentControl), new PropertyMetadata(null));

        public static readonly DependencyProperty MenuStateProperty =
            DependencyProperty.Register("MenuState", typeof(MenuState), typeof(SlideInMenuContentControl), new PropertyMetadata(MenuState.Both, OnMenuStateChanged));

        public static readonly DependencyProperty LeftSideMenuWidthProperty =
            DependencyProperty.Register("LeftSideMenuWidth", typeof(double), typeof(SlideInMenuContentControl), new PropertyMetadata(250.0));

        private const string ElementLeftSideMenu = "ContentLeftSideMenu";
        private const string ElementContentSelector = "ContentSelector";
        private const string ElementDisableContentOverlay = "DisableContentOverlay";

        private FrameworkElement leftSideMenu;
        private Selector contentSelector;
        private Border disableContentOverlay;

        public SlideInMenuContentControl()
        {
            this.DefaultStyleKey = typeof(SlideInMenuContentControl);
        }

        public double LeftSideMenuWidth
        {
            get { return (double)GetValue(LeftSideMenuWidthProperty); }
            set { SetValue(LeftSideMenuWidthProperty, value); }
        }

        public MenuState MenuState
        {
            get { return (MenuState)GetValue(MenuStateProperty); }
            set { SetValue(MenuStateProperty, value); }
        }

        public object LeftMenuContent
        {
            get { return (object)GetValue(LeftMenuContentProperty); }
            set { SetValue(LeftMenuContentProperty, value); }
        }

        public void GoToMenuState(ActiveState state)
        {
            contentSelector.SelectedIndex = 0;
        }

        protected override void OnApplyTemplate()
        {
            base.OnApplyTemplate();

            contentSelector = GetTemplateChild(ElementContentSelector) as Selector;
            leftSideMenu = GetTemplateChild(ElementLeftSideMenu) as FrameworkElement;
            disableContentOverlay = GetTemplateChild(ElementDisableContentOverlay) as Border;
            ////var contentFrame = GetTemplateChild("ContentFrame") as FrameworkElement;
            ////contentFrame.Width = Window.Current.Bounds.Width;

            contentSelector.SelectionChanged += ContentSelector_SelectionChanged;
            SetMenuVisibility();
        }

        private static void OnMenuStateChanged(DependencyObject d, DependencyPropertyChangedEventArgs e)
        {
            var control = d as SlideInMenuContentControl;
            control.SetMenuVisibility();
        }

        private void SetMenuVisibility()
        {
            if (leftSideMenu != null && contentSelector != null)
            {
                leftSideMenu.Visibility = Visibility.Visible;
                contentSelector.SelectedIndex = 1;
            }
        }

        private void ContentSelector_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            switch (MenuState)
            {
                case MenuState.Left:
                    if (contentSelector.SelectedIndex == 0)
                    {
                        disableContentOverlay.Visibility = Visibility.Visible;
                    }
                    else
                    {
                        disableContentOverlay.Visibility = Visibility.Collapsed;
                    }
                    break;
                case MenuState.Both:
                    if (contentSelector.SelectedIndex == 0 || contentSelector.SelectedIndex == 2)
                    {
                        disableContentOverlay.Visibility = Visibility.Visible;
                    }
                    else
                    {
                        disableContentOverlay.Visibility = Visibility.Collapsed;
                    }
                    break;
                default:
                    break;
            }
        }
    }
}
