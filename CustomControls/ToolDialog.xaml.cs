using Grappbox.ViewModel;
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

namespace Grappbox.CustomControls
{
    public sealed partial class ToolDialog : ContentDialog
    {
        public SolidColorBrush Stroke { get; set; }
        public SolidColorBrush Fill { get; set; }
        public WhiteboardTool SelectedTool { get; set; }
        public static readonly WhiteBoardToolItem[] ToolMap = new WhiteBoardToolItem[] {
            new WhiteBoardToolItem( 0, "Rectangle", WhiteboardTool.RECTANGLE, ""),
            new WhiteBoardToolItem( 1, "Ellipse", WhiteboardTool.ELLIPSE, ""),
            new WhiteBoardToolItem( 2, "Lozenge", WhiteboardTool.LOZENGE, ""),
            new WhiteBoardToolItem( 3, "Line", WhiteboardTool.LINE, ""),
            new WhiteBoardToolItem( 4, "Handwriting", WhiteboardTool.HANDWRITING, ""),
            new WhiteBoardToolItem( 5, "Text", WhiteboardTool.TEXT, ""),
            new WhiteBoardToolItem( 6, "Erazer", WhiteboardTool.ERAZER, ""),
            new WhiteBoardToolItem( 7, "Pointer", WhiteboardTool.POINTER, "")
        };
        public ToolDialog(WhiteboardTool tool, SolidColorBrush stroke, SolidColorBrush fill)
        {
            this.FullSizeDesired = true;
            this.InitializeComponent();
            SelectedTool = tool;
            this.Stroke = stroke;
            this.Fill = fill;
        }


        public class WhiteBoardToolItem
        {
            public int Id { get; set; }
            public string Name { get; set; }
            public WhiteboardTool Tool { get; set; }
            public string Icon { get; set; }
            public WhiteBoardToolItem(int id, string name, WhiteboardTool tool, string icon)
            {
                Id = id;
                Name = name;
                Tool = tool;
                Icon = icon;
            }
        }

        private void GridViewTools_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            var gv = sender as GridView;
            this.SelectedTool = ToolMap[gv.SelectedIndex].Tool;
            this.Hide();
        }
    }
}
