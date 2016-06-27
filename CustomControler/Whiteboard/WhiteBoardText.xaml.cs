using System;
using System.Collections.Generic;
using System.Diagnostics;
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

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class WhiteBoardText : UserControl
    {
        public static readonly DependencyProperty TextConfirmedProperty =
            DependencyProperty.Register("IsTextConfirmed", typeof(bool), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty TextEnteredProperty =
            DependencyProperty.Register("TextEntered", typeof(string), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty IsOpenedProperty =
            DependencyProperty.Register("IsOpened", typeof(bool), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty IsBoldProperty =
            DependencyProperty.Register("IsBold", typeof(bool), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty IsItalicProperty =
            DependencyProperty.Register("IsItalic", typeof(bool), typeof(WhiteBoardText), null);
   /*     public static readonly DependencyProperty FontSizeProperty =
            DependencyProperty.Register("FontSizeProp", typeof(int), typeof(WhiteBoardText), null);
        public int FontSizeProp
        {
            get { return (int)GetValue(FontSizeProperty); }
            set { SetValue(FontSizeProperty, value); }
        }*/
        public bool IsItalic
        {
            get { return (bool)GetValue(IsItalicProperty); }
            set { SetValue(IsItalicProperty, value); }
        }
        public bool IsBold
        {
            get { return (bool)GetValue(IsBoldProperty); }
            set { SetValue(IsBoldProperty, value); }
        }
        public bool IsOpened
        {
            get { return (bool)GetValue(IsOpenedProperty); }
            set { SetValue(IsOpenedProperty, value); }
        }
        public bool IsTextConfirmed
        {
            get { return (bool)GetValue(TextConfirmedProperty); }
            set { SetValue(TextConfirmedProperty, value); }
        }
        public string TextEntered
        {
            get { return (string)GetValue(TextEnteredProperty); }
            set { SetValue(TextEnteredProperty, value); }
        }
        public WhiteBoardText()
        {
            IsTextConfirmed = false;
            IsOpened = false;
            this.InitializeComponent();
        }

        private void ConfirmText_Click(object sender, RoutedEventArgs e)
        {
            TextEntered = textBlock.Text;
            textBlock.Text = "";
            IsTextConfirmed = true;
            IsTextConfirmed = false;
            IsOpened = false;
            BoldCheckBox.IsChecked = false;
            ItalicCheckBox.IsChecked = false;
        }
        private void CancelText_Click(object sender, RoutedEventArgs e)
        {
            IsTextConfirmed = false;
            IsOpened = false;
            BoldCheckBox.IsChecked = false;
            ItalicCheckBox.IsChecked = false;
            textBlock.Text = "";
        }
        private void textBox_TextChanged(object sender, TextChangedEventArgs e)
        {
            if (textBlock.Text.Length == 0)
                ConfirmText.IsEnabled = false;
            else
                ConfirmText.IsEnabled = true;
        }
    }
}