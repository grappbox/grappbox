using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Threading;
using System.Threading.Tasks;
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
using Windows.UI.Xaml.Shapes;
// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class ToolPan : UserControl
    {
        public WhiteboardTool SelectedTool { get; set; }
        public int SelectedImage { get; set; }
        private static readonly List<WhiteboardTool> toolList = new List<WhiteboardTool>()
        {
            WhiteboardTool.RECTANGLE,
            WhiteboardTool.ELLIPSE,
            WhiteboardTool.LOZENGE,
            WhiteboardTool.LINE,
            WhiteboardTool.HANDWRITING,
            WhiteboardTool.TEXT,
            WhiteboardTool.ERAZER,
            WhiteboardTool.POINTER
        };

        private static readonly List<string> buttonsBinding = new List<string>()
        {
            "/Assets/rectangle.png",
            "/Assets/ellipse.png",
            "/Assets/lozenge.png",
            "/Assets/line.png",
            "/Assets/handwrite.png",
            "/Assets/text.png",
            "/Assets/erazer.png",
            "/Assets/pointer.png"
        };

        public ToolPan()
        {
            this.InitializeComponent();
            SelectedTool = WhiteboardTool.NONE;
            GridViewTools.ItemsSource = buttonsBinding;
        }

        public async System.Threading.Tasks.Task WaitForSelect(CancellationToken tok)
        {
            await Task.Run(() =>
            {
                while (SelectedTool == WhiteboardTool.NONE) {}
            }, tok);
        }

        private void GridViewTools_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            SelectedImage = GridViewTools.SelectedIndex;
            SelectedTool = toolList[GridViewTools.SelectedIndex];
        }
    }
}
