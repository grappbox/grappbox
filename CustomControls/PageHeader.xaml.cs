using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Grappbox.View;
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

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Page header user control
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.UserControl" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class PageHeader : UserControl
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="PageHeader"/> class.
        /// </summary>
        public PageHeader()
        {
            this.InitializeComponent();
        }

        /// <summary>
        /// Gets or sets the content of the header.
        /// </summary>
        /// <value>
        /// The content of the header.
        /// </value>
        public string HeaderContent
        {
            get { return (string)GetValue(HeaderContentProperty); }
            set { SetValue(HeaderContentProperty, value); }
        }

        // Using a DependencyProperty as the backing store for HeaderContent.  This enables animation, styling, binding, etc...
        public static readonly DependencyProperty HeaderContentProperty =
            DependencyProperty.Register("HeaderContent", typeof(string), typeof(PageHeader), new PropertyMetadata(DependencyProperty.UnsetValue));
    }
}
