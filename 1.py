import os

def list_files(startpath):
    """
    递归显示目录结构
    :param startpath: 起始路径
    """
    for root, dirs, files in os.walk(startpath):
        # 计算当前目录的层级
        level = root.replace(startpath, '').count(os.sep)
        indent = ' ' * 4 * level  # 每个级别增加缩进
        print(f"{indent}[DIR] {os.path.basename(root)}")  # 显示目录名称
        subindent = ' ' * 4 * (level + 1)  # 子目录的缩进
        for file in files:
            print(f"{subindent}[FILE] {file}")  # 显示文件名称

if __name__ == "__main__":
    # 设置后端项目的根目录（Windows 下的路径）
    startpath = r"C:\path\to\your\project"  # 请将路径改为你的项目目录路径
    list_files(startpath)
