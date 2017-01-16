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

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Pivot Tab header user control
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.UserControl" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class TabHeader : UserControl
    {
        public static readonly DependencyProperty GlyphProperty = DependencyProperty.Register("Glyph", typeof(string), typeof(TabHeader), null);

        public string Glyph
        {
            get { return GetValue(GlyphProperty) as string; }
            set { SetValue(GlyphProperty, value); }
        }

        public static readonly DependencyProperty LabelProperty = DependencyProperty.Register("Label", typeof(string), typeof(TabHeader), null);

        public string Label
        {
            get { return GetValue(LabelProperty) as string; }
            set { SetValue(LabelProperty, value); }
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="TabHeader"/> class.
        /// </summary>
        public TabHeader()
        {
            this.InitializeComponent();
            this.DataContext = this;
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="TabHeader"/> class.
        /// </summary>
        /// <param name="label">The label.</param>
        /// <param name="glyph">The glyph.</param>
        public TabHeader(string label, string glyph)
        {
            this.InitializeComponent();
            this.DataContext = this;
            Label = label;
            Glyph = glyph;
        }
    }
}