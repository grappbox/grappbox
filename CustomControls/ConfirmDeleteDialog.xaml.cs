using Grappbox.Helpers;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
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

// Pour plus d'informations sur le modèle d'élément Boîte de dialogue de contenu, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public sealed partial class ConfirmDeleteDialog : ContentDialog
    {
        private SolidColorBrush myPrimaryColor;
        private string myTitle = "";
        private string myContent = "";
        public bool ConfirmDelete = false;
        public ConfirmDeleteDialog(string title, string content, SolidColorBrush primaryColor = null)
        {
            myPrimaryColor = primaryColor ?? SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush");
            myTitle = title;
            myContent = content;
            FullSizeDesired = true;
            this.InitializeComponent();
            this.Opened += ConfirmDeleteDialog_Opened;
        }

        private void ConfirmDeleteDialog_Opened(ContentDialog sender, ContentDialogOpenedEventArgs args)
        {
            ConfirmDelete = false;
        }

        private void ConfirmClick(object sender, RoutedEventArgs e)
        {
            ConfirmDelete = true;
            this.Hide();
        }
        private void CancelClick(object sender, RoutedEventArgs e)
        {
            this.Hide();
        }
    }
}
