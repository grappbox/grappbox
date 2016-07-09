using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
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
    public sealed partial class ToolsPan : UserControl
    {
        public WhiteboardTool SelectedTool { get; set; }
        private static readonly Dictionary<string, WhiteboardTool> toolMap = new Dictionary<string, WhiteboardTool>()
        {
            { "b1", WhiteboardTool.RECTANGLE },
            { "b2", WhiteboardTool.ELLIPSE },
            { "b3", WhiteboardTool.LOZENGE },
            { "b4", WhiteboardTool.LINE },
            { "b5", WhiteboardTool.HANDWRITING },
            { "b6", WhiteboardTool.TEXT },
            { "b7", WhiteboardTool.ERAZER },
            { "b8", WhiteboardTool.POINTER },
        };
        private static readonly Dictionary<string, string> buttonsBinding = new Dictionary<string, string>()
        {
            { "b1", WhiteboardTool.RECTANGLE },
            { "b2", WhiteboardTool.ELLIPSE },
            { "b3", WhiteboardTool.LOZENGE },
            { "b4", WhiteboardTool.LINE },
            { "b5", WhiteboardTool.HANDWRITING },
            { "b6", WhiteboardTool.TEXT },
            { "b7", WhiteboardTool.ERAZER },
            { "b8", WhiteboardTool.POINTER },
        };
        public ToolsPan()
        {
            SelectedTool = WhiteboardTool.NONE;
            GridViewTools.
        }

        public async System.Threading.Tasks.Task WaitForSelect()
        {
            await Task.Run(() =>
            {
                while (SelectedTool == WhiteboardTool.NONE) ;
            });
        }
        private void Button_Click(object sender, RoutedEventArgs e)
        {
            Button b = sender as Button;
            SelectedTool = toolMap[b.Name];
        }

        private void GridViewTools_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {

        }
    }
}
