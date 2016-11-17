using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// Pour plus d'informations sur le modèle d'élément Boîte de dialogue de contenu, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace GrappBox.CustomControls
{
    public sealed partial class LoaderDialog : ContentDialog
    {
        /// <summary>
        /// Instanciate a LoaderDialog wich display dialog box with  progress ring.
        /// <para>
        /// </para>
        /// </summary>
        public LoaderDialog()
        {
            this.InitDefault();
            this.InitializeComponent();
        }

        /// <summary>
        /// Instanciate a LoaderDialog wich display dialog box with a progress ring.
        /// <para>
        /// colorBrush: The foreground brush of the progress ring.
        /// </para>
        /// </summary>
        public LoaderDialog(SolidColorBrush colorBrush)
        {
            this.InitDefault();
            this.InitializeComponent();
            progressRing.Foreground = colorBrush;
            loadingText.Foreground = colorBrush;
        }

        /// <summary>
        /// Instanciate a LoaderDialog wich display dialog box with a progress ring.
        /// <para>
        /// color: The foreground brush of the progress ring.
        /// </para>
        /// </summary>
        public LoaderDialog(Color color)
        {
            this.InitDefault();
            this.InitializeComponent();
            progressRing.Foreground = new SolidColorBrush(color);
            loadingText.Foreground = new SolidColorBrush(color);
        }

        private void InitDefault()
        {
            this.FullSizeDesired = true;
        }
    }
}